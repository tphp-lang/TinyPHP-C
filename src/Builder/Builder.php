<?php

declare(strict_types=1);

namespace Tphp\Builder;

use Tphp\Checker\Checker;
use Tphp\Errors\Errors;
use Tphp\Gen\Gen;
use Tphp\Parser\Parser;
use Tphp\Pref\Pref;
use Tphp\Table\Table;

/**
 * 编译流水线：
 *
 *   Pref → Scanner+Parser → Checker（两遍）→ Gen → C 源码 → Cc（TCC/GCC/Clang）
 *
 * 各阶段只通过显式产物衔接：File[] → (Table + 标注后的 AST) → C 字符串。
 */
final class Builder
{
    public function __construct(private readonly Pref $pref) {}

    public function run(): int
    {
        foreach ($this->pref->inputs as $input) {
            if (!is_file($input)) {
                fwrite(STDERR, "TinyPHP: 找不到文件 {$input}\n");
                return 1;
            }
        }

        // 多文件（与旧版机制一致）：入口 = 含全局 class Main 的文件（可只有一个），
        // 第一个参数仅用于输出命名；解析顺序为辅助文件在前、入口在后。
        $entryIndex = null;
        $sources = [];
        foreach ($this->pref->inputs as $idx => $input) {
            $src = file_get_contents($input);
            if ($src === false) {
                fwrite(STDERR, "TinyPHP: 无法读取 {$input}\n");
                return 1;
            }
            if (preg_match('/^\s*class\s+Main\b/m', $src) === 1) {
                if ($entryIndex !== null) {
                    fwrite(STDERR, "TinyPHP: 错误：发现多个包含 class Main 的入口文件（{$this->pref->inputs[$entryIndex]} 与 {$input}）\n");
                    return 1;
                }
                $entryIndex = $idx;
            }
            $sources[$idx] = $src;
        }

        $errors = new Errors();
        $table = new Table();
        $parser = new Parser($errors);

        $ordered = array_keys($sources);
        if ($entryIndex !== null) {
            $ordered = array_diff($ordered, [$entryIndex]);
            $ordered[] = $entryIndex; // 入口最后解析
        }

        $files = [];
        foreach ($ordered as $idx) {
            $input = $this->pref->inputs[$idx];
            $files[] = $parser->parseFile(str_replace('\\', '/', $input), $sources[$idx], [
                'os' => $this->pref->os,
                'arch' => $this->pref->arch,
                'cc' => $this->pref->cc,
            ]);
        }
        if ($errors->hasErrors()) {
            return $this->report($errors);
        }

        $entryPath = $entryIndex !== null ? str_replace('\\', '/', $this->pref->inputs[$entryIndex]) : str_replace('\\', '/', $this->pref->inputs[0]);
        (new Checker($table, $errors))->check($files, $entryPath, $this->pref->noMain);
        $this->printWarnings($errors);
        if ($errors->hasErrors()) {
            return $this->report($errors);
        }

        $cSource = (new Gen($table, $errors, $this->pref->noMain, $this->pref->memStats))->generate($files);

        // 输出路径：可执行文件/动态库默认当前目录（根目录）；C 源码默认放 build/ 目录。
        // -o 显式指定输出路径时，C 源码与产物同目录。
        // 命名用入口文件（含全局 class Main 的文件；`.` 展开等场景下与参数顺序无关）。
        $base = pathinfo($this->pref->inputs[$entryIndex ?? 0], PATHINFO_FILENAME);
        if ($this->pref->shared) {
            $exePath = $this->pref->output ?? $base . ($this->pref->os === 'windows' ? '.dll' : '.so');
        } else {
            $exePath = $this->pref->output ?? $base . ($this->pref->os === 'windows' ? '.exe' : '');
        }
        $exeDir = dirname($exePath);
        $exeName = pathinfo($exePath, PATHINFO_FILENAME);
        $cDir = $this->pref->output !== null ? $exeDir : ($exeDir === '.' ? 'build' : $exeDir . '/build');
        $cPath = ($cDir === '.' ? '' : $cDir . '/') . $exeName . '.c';

        $dir = dirname($cPath);
        if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
            fwrite(STDERR, "TinyPHP: 无法创建目录 {$dir}\n");
            return 1;
        }
        file_put_contents($cPath, $cSource);
        echo "生成 C 源码: {$cPath}\n";

        $emitC = $this->pref->emitC;
        if ($this->pref->noMain && !$this->pref->shared && !$emitC) {
            // 库模式没有 main，无法链接可执行文件：退回只输出 C
            echo "提示：--no-main 未生成 main()，仅输出 C 源码（可加 shared 命令生成动态库）\n";
            $emitC = true;
        }
        if ($emitC) {
            return 0;
        }

        $exe = Cc::compile($this->pref, $cPath, $exePath, dirname(__DIR__, 2) . '/runtime', $this->collectCFlags($files), $this->collectFlagSources($files));
        if ($exe === null) {
            return 1;
        }
        echo "编译完成: {$exe}\n";

        if ($this->pref->run) {
            if (!$this->canRunHere()) {
                fwrite(STDERR, "TinyPHP: 无法在本机运行 {$this->pref->os}/{$this->pref->arch} 目标的产物\n");
                return 1;
            }
            return $this->runExe($exe);
        }
        return 0;
    }

    /** 收集全部文件的 #flag 参数。 @return list<string> */
    private function collectCFlags(array $files): array
    {
        $flags = [];
        foreach ($files as $file) {
            foreach ($file->cflags as $flag) {
                $flags[] = $flag;
            }
        }
        return $flags;
    }

    /** 收集 #flag 中引用的 .c 源文件（加入编译列表）。 @return list<string> */
    private function collectFlagSources(array $files): array
    {
        $sources = [];
        foreach ($files as $file) {
            foreach ($file->cflags as $flag) {
                foreach (preg_split('/\s+/', trim($flag)) ?: [] as $token) {
                    if (str_ends_with($token, '.c')) {
                        if (!is_file($token)) {
                            fwrite(STDERR, "TinyPHP: {$file->path}: #flag 引用的源文件不存在 {$token}\n");
                            exit(1);
                        }
                        $sources[] = $token;
                    }
                }
            }
        }
        return $sources;
    }

    /** run 命令仅对本机目标可用。 */
    private function canRunHere(): bool
    {
        $hostOs = strtolower(PHP_OS_FAMILY); // windows / linux / darwin
        if ($this->pref->os !== $hostOs) {
            return false;
        }
        return $this->pref->arch === Pref::normalizeArch(php_uname('m'));
    }

    private function report(Errors $errors): int
    {
        fwrite(STDERR, $errors->report());
        fwrite(STDERR, '共 ' . $errors->count() . " 个错误\n");
        return 1;
    }

    /** 警告不阻断编译，统一在检查完成后输出。 */
    private function printWarnings(Errors $errors): void
    {
        $text = $errors->reportWarnings();
        if ($text === '') {
            return;
        }
        fwrite(STDERR, $text);
        fwrite(STDERR, '共 ' . $errors->warningCount() . " 个警告\n");
    }

    private function runExe(string $exe): int
    {
        $code = 0;
        passthru(escapeshellarg($exe), $code);
        return $code;
    }
}
