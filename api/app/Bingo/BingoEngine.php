<?php

namespace App\Bingo;

class BingoEngine
{
    /** @var array<int, array{0:int,1:int}> */
    public const COLUMN_RANGES = [
        [1, 15],
        [16, 30],
        [31, 45],
        [46, 60],
        [61, 75],
    ];

    /** @return array<int, array<int, int>> 5x5, center 0 = FREE */
    public static function generateCard(): array
    {
        $grid = array_fill(0, 5, array_fill(0, 5, 0));

        for ($col = 0; $col < 5; $col++) {
            [$min, $max] = self::COLUMN_RANGES[$col];
            $pool = range($min, $max);
            shuffle($pool);
            $nums = array_slice($pool, 0, 5);
            sort($nums);
            for ($row = 0; $row < 5; $row++) {
                $grid[$row][$col] = $nums[$row];
            }
        }

        $grid[2][2] = 0;

        return $grid;
    }

    /** @return list<int> */
    public static function createDrawPool(): array
    {
        $pool = range(1, 75);
        shuffle($pool);

        return array_values($pool);
    }

    /**
     * @param  array<int, array<int, int>>  $card
     * @param  list<int>  $drawn
     * @param  list<string>  $patterns  row|column|diagonal
     * @return array{won: bool, pattern?: string, line?: int}
     */
    public static function checkWin(array $card, array $drawn, array $patterns): array
    {
        $set = array_fill_keys($drawn, true);
        $marked = [];

        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 5; $c++) {
                $n = $card[$r][$c];
                $marked[$r][$c] = ($r === 2 && $c === 2) || ($n > 0 && isset($set[$n]));
            }
        }

        if (in_array('row', $patterns, true)) {
            for ($r = 0; $r < 5; $r++) {
                if (! in_array(false, $marked[$r], true)) {
                    return ['won' => true, 'pattern' => 'row', 'line' => $r];
                }
            }
        }

        if (in_array('column', $patterns, true)) {
            for ($c = 0; $c < 5; $c++) {
                $ok = true;
                for ($r = 0; $r < 5; $r++) {
                    if (! $marked[$r][$c]) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    return ['won' => true, 'pattern' => 'column', 'line' => $c];
                }
            }
        }

        if (in_array('diagonal', $patterns, true)) {
            $d1 = true;
            $d2 = true;
            for ($i = 0; $i < 5; $i++) {
                if (! $marked[$i][$i]) {
                    $d1 = false;
                }
                if (! $marked[$i][4 - $i]) {
                    $d2 = false;
                }
            }
            if ($d1) {
                return ['won' => true, 'pattern' => 'diagonal', 'line' => 0];
            }
            if ($d2) {
                return ['won' => true, 'pattern' => 'diagonal', 'line' => 1];
            }
        }

        return ['won' => false];
    }
}
