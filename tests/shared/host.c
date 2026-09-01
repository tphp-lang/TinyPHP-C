/* 宿主程序：加载 TinyPHP 生成的动态库，按 #[export] 符号调用（tests/shared.php 使用）。
 *
 * 用法: host.exe <dll路径>
 * 预期输出:
 *   42            ← c_add(20, 22)
 *   no-tphp_add   ← 默认符号 tphp_add 已被 #[export] 重命名，不应存在
 */
#include <windows.h>
#include <stdio.h>

typedef int (*add_fn)(int, int);

int main(int argc, char **argv)
{
    if (argc < 2) {
        printf("usage: host.exe <dll>\n");
        return 2;
    }
    HMODULE lib = LoadLibraryA(argv[1]);
    if (!lib) {
        printf("LOAD-FAIL\n");
        return 1;
    }
    add_fn add = (add_fn)(void *)GetProcAddress(lib, "c_add");
    if (!add) {
        printf("MISS c_add\n");
        return 1;
    }
    printf("%d\n", add(20, 22));
    if (GetProcAddress(lib, "tphp_add")) {
        printf("BAD tphp_add-visible\n");
        return 1;
    }
    printf("no-tphp_add\n");
    FreeLibrary(lib);
    return 0;
}
