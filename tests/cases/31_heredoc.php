<?php

// heredoc / nowdoc：插值 heredoc 与原文 nowdoc，多行内容
// expect:
// title=name, id=7
// end
// raw $name and {<noop>} stay
// line1
// line2

class Main
{
    public function main(): void
    {
        string $name = "name";
        int $id = 7;
        string $h = <<<EOT
title=$name, id={$id}
EOT;
        echo $h, "\n";
        echo "end", "\n";

        string $n = <<<'RAW'
raw $name and {<noop>} stay
RAW;
        echo $n, "\n";

        string $multi = <<<M
line1
line2
M;
        echo $multi, "\n";
    }
}
