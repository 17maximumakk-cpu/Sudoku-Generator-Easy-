<?php
// sudoku_generator.php — PHP версия

class SudokuGenerator {
    private $size = 9;
    private $boxSize = 3;
    private $board = [];
    private $solution = [];
    private $cellsToRemove;

    public function __construct($difficulty = 'easy') {
        $this->board = array_fill(0, $this->size, array_fill(0, $this->size, 0));
        $this->solution = array_fill(0, $this->size, array_fill(0, $this->size, 0));
        $this->cellsToRemove = $difficulty == 'easy' ? 35 : ($difficulty == 'medium' ? 45 : 55);
    }

    public function generate() {
        $this->solve($this->board);
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                $this->solution[$i][$j] = $this->board[$i][$j];
            }
        }
        $this->removeCells();
        return $this->board;
    }

    private function solve(&$board) {
        $empty = $this->findEmpty($board);
        if ($empty === null) return true;
        list($row, $col) = $empty;
        $nums = range(1, 9);
        shuffle($nums);
        foreach ($nums as $num) {
            if ($this->isValid($board, $row, $col, $num)) {
                $board[$row][$col] = $num;
                if ($this->solve($board)) return true;
                $board[$row][$col] = 0;
            }
        }
        return false;
    }

    private function findEmpty($board) {
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                if ($board[$i][$j] == 0) return [$i, $j];
            }
        }
        return null;
    }

    private function isValid($board, $row, $col, $num) {
        for ($j = 0; $j < $this->size; $j++) {
            if ($board[$row][$j] == $num) return false;
        }
        for ($i = 0; $i < $this->size; $i++) {
            if ($board[$i][$col] == $num) return false;
        }
        $startRow = intdiv($row, $this->boxSize) * $this->boxSize;
        $startCol = intdiv($col, $this->boxSize) * $this->boxSize;
        for ($i = $startRow; $i < $startRow + $this->boxSize; $i++) {
            for ($j = $startCol; $j < $startCol + $this->boxSize; $j++) {
                if ($board[$i][$j] == $num) return false;
            }
        }
        return true;
    }

    private function removeCells() {
        $cells = [];
        for ($i = 0; $i < $this->size; $i++) {
            for ($j = 0; $j < $this->size; $j++) {
                $cells[] = [$i, $j];
            }
        }
        shuffle($cells);
        $removed = 0;
        foreach ($cells as $cell) {
            if ($removed >= $this->cellsToRemove) break;
            $this->board[$cell[0]][$cell[1]] = 0;
            $removed++;
        }
    }

    public function printBoard($showSolution = false) {
        $board = $showSolution ? $this->solution : $this->board;
        echo "┌───────┬───────┬───────┐\n";
        for ($i = 0; $i < $this->size; $i++) {
            echo "│";
            for ($j = 0; $j < $this->size; $j++) {
                if ($board[$i][$j] == 0) {
                    echo " . ";
                } else {
                    echo " {$board[$i][$j]} ";
                }
                if ($j % 3 == 2 && $j < $this->size - 1) echo "│";
            }
            echo "│\n";
            if ($i % 3 == 2 && $i < $this->size - 1) {
                echo "├───────┼───────┼───────┤\n";
            }
        }
        echo "└───────┴───────┴───────┘\n";
    }

    public function saveTXT($filename = 'sudoku_puzzle.txt') {
        $content = "";
        foreach ($this->board as $row) {
            $content .= implode(' ', array_map(function($c) { return $c == 0 ? '.' : $c; }, $row)) . "\n";
        }
        file_put_contents($filename, $content);
        echo "💾 Сохранено: $filename\n";
    }

    public function saveJSON($filename = 'sudoku_puzzle.json') {
        $data = ['puzzle' => $this->board, 'solution' => $this->solution, 'difficulty' => 'easy'];
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        echo "💾 Сохранено: $filename\n";
    }
}

function main() {
    echo "🧩 Sudoku Generator (Easy) (PHP)\n";
    $gen = new SudokuGenerator('easy');
    $gen->generate();

    echo "\nГоловоломка:\n";
    $gen->printBoard();

    echo "\nПоказать решение? (y/n): ";
    $ans = trim(fgets(STDIN));
    if (strtolower($ans) == 'y') {
        echo "\nРешение:\n";
        $gen->printBoard(true);
    }

    $gen->saveTXT();
    $gen->saveJSON();
}

main();
?>
