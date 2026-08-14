

### 1. `sudoku_generator.py` (Python)

```python
# sudoku_generator.py — Python версия

import random
import json
import sys
from colorama import init, Fore, Style

init(autoreset=True)

class SudokuGenerator:
    def __init__(self, difficulty='easy'):
        self.difficulty = difficulty
        self.size = 9
        self.box_size = 3
        self.board = [[0 for _ in range(self.size)] for _ in range(self.size)]
        self.solution = [[0 for _ in range(self.size)] for _ in range(self.size)]
        self.cells_to_remove = 35 if difficulty == 'easy' else 45 if difficulty == 'medium' else 55

    def generate(self):
        """Генерирует полное решённое поле."""
        self._solve(self.board)
        for i in range(self.size):
            for j in range(self.size):
                self.solution[i][j] = self.board[i][j]

        # Удаляем клетки для создания головоломки
        self._remove_cells()
        return self.board

    def _solve(self, board):
        """Решает судоку с помощью backtracking."""
        empty = self._find_empty(board)
        if not empty:
            return True
        row, col = empty
        numbers = list(range(1, 10))
        random.shuffle(numbers)
        for num in numbers:
            if self._is_valid(board, row, col, num):
                board[row][col] = num
                if self._solve(board):
                    return True
                board[row][col] = 0
        return False

    def _find_empty(self, board):
        for i in range(self.size):
            for j in range(self.size):
                if board[i][j] == 0:
                    return (i, j)
        return None

    def _is_valid(self, board, row, col, num):
        # Проверка строки
        for j in range(self.size):
            if board[row][j] == num:
                return False
        # Проверка столбца
        for i in range(self.size):
            if board[i][col] == num:
                return False
        # Проверка бокса 3x3
        start_row = (row // self.box_size) * self.box_size
        start_col = (col // self.box_size) * self.box_size
        for i in range(start_row, start_row + self.box_size):
            for j in range(start_col, start_col + self.box_size):
                if board[i][j] == num:
                    return False
        return True

    def _remove_cells(self):
        """Удаляет клетки для создания головоломки."""
        cells = [(i, j) for i in range(self.size) for j in range(self.size)]
        random.shuffle(cells)
        removed = 0
        for row, col in cells:
            if removed >= self.cells_to_remove:
                break
            self.board[row][col] = 0
            removed += 1

    def print_board(self, show_solution=False):
        """Выводит поле в красивом формате."""
        board = self.solution if show_solution else self.board
        print("┌───────┬───────┬───────┐")
        for i in range(self.size):
            line = "│"
            for j in range(self.size):
                if board[i][j] == 0:
                    line += " . "
                else:
                    line += f" {Fore.GREEN}{board[i][j]}{Style.RESET_ALL} " if not show_solution else f" {board[i][j]} "
                if j % 3 == 2 and j < self.size - 1:
                    line += "│"
            line += "│"
            print(line)
            if i % 3 == 2 and i < self.size - 1:
                print("├───────┼───────┼───────┤")
        print("└───────┴───────┴───────┘")

    def save_txt(self, filename="sudoku_puzzle.txt"):
        with open(filename, 'w') as f:
            for row in self.board:
                f.write(' '.join(str(cell) if cell != 0 else '.' for cell in row) + '\n')
        print(f"💾 Сохранено: {filename}")

    def save_json(self, filename="sudoku_puzzle.json"):
        data = {
            "puzzle": self.board,
            "solution": self.solution,
            "difficulty": self.difficulty
        }
        with open(filename, 'w') as f:
            json.dump(data, f, indent=2)
        print(f"💾 Сохранено: {filename}")

    def solve(self):
        """Решает головоломку (если возможно)."""
        board_copy = [row[:] for row in self.board]
        if self._solve(board_copy):
            return board_copy
        return None

def main():
    print("🧩 Sudoku Generator (Easy) (Python)")
    gen = SudokuGenerator('easy')
    gen.generate()

    print("\nГоловоломка:")
    gen.print_board()

    print("\nПоказать решение? (y/n): ", end="")
    if input().strip().lower() == 'y':
        print("\nРешение:")
        gen.print_board(show_solution=True)

    gen.save_txt()
    gen.save_json()

if __name__ == "__main__":
    main()
