// sudoku_generator.js — JavaScript версия

const fs = require('fs');

class SudokuGenerator {
    constructor(difficulty = 'easy') {
        this.size = 9;
        this.boxSize = 3;
        this.board = Array.from({ length: 9 }, () => Array(9).fill(0));
        this.solution = Array.from({ length: 9 }, () => Array(9).fill(0));
        this.cellsToRemove = difficulty === 'easy' ? 35 : difficulty === 'medium' ? 45 : 55;
    }

    generate() {
        this._solve(this.board);
        for (let i = 0; i < this.size; i++) {
            for (let j = 0; j < this.size; j++) {
                this.solution[i][j] = this.board[i][j];
            }
        }
        this._removeCells();
        return this.board;
    }

    _solve(board) {
        const empty = this._findEmpty(board);
        if (!empty) return true;
        const [row, col] = empty;
        const nums = this._shuffle([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        for (const num of nums) {
            if (this._isValid(board, row, col, num)) {
                board[row][col] = num;
                if (this._solve(board)) return true;
                board[row][col] = 0;
            }
        }
        return false;
    }

    _findEmpty(board) {
        for (let i = 0; i < this.size; i++) {
            for (let j = 0; j < this.size; j++) {
                if (board[i][j] === 0) return [i, j];
            }
        }
        return null;
    }

    _isValid(board, row, col, num) {
        for (let j = 0; j < this.size; j++) {
            if (board[row][j] === num) return false;
        }
        for (let i = 0; i < this.size; i++) {
            if (board[i][col] === num) return false;
        }
        const startRow = Math.floor(row / this.boxSize) * this.boxSize;
        const startCol = Math.floor(col / this.boxSize) * this.boxSize;
        for (let i = startRow; i < startRow + this.boxSize; i++) {
            for (let j = startCol; j < startCol + this.boxSize; j++) {
                if (board[i][j] === num) return false;
            }
        }
        return true;
    }

    _removeCells() {
        const cells = [];
        for (let i = 0; i < this.size; i++) {
            for (let j = 0; j < this.size; j++) {
                cells.push([i, j]);
            }
        }
        this._shuffle(cells);
        let removed = 0;
        for (const [row, col] of cells) {
            if (removed >= this.cellsToRemove) break;
            this.board[row][col] = 0;
            removed++;
        }
    }

    _shuffle(arr) {
        for (let i = arr.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [arr[i], arr[j]] = [arr[j], arr[i]];
        }
        return arr;
    }

    printBoard(showSolution = false) {
        const board = showSolution ? this.solution : this.board;
        console.log('┌───────┬───────┬───────┐');
        for (let i = 0; i < this.size; i++) {
            let line = '│';
            for (let j = 0; j < this.size; j++) {
                line += board[i][j] === 0 ? ' . ' : ` ${board[i][j]} `;
                if (j % 3 === 2 && j < this.size - 1) line += '│';
            }
            line += '│';
            console.log(line);
            if (i % 3 === 2 && i < this.size - 1) {
                console.log('├───────┼───────┼───────┤');
            }
        }
        console.log('└───────┴───────┴───────┘');
    }

    saveTXT(filename = 'sudoku_puzzle.txt') {
        let content = '';
        for (const row of this.board) {
            content += row.map(c => c === 0 ? '.' : c).join(' ') + '\n';
        }
        fs.writeFileSync(filename, content);
        console.log(`💾 Сохранено: ${filename}`);
    }

    saveJSON(filename = 'sudoku_puzzle.json') {
        const data = { puzzle: this.board, solution: this.solution, difficulty: 'easy' };
        fs.writeFileSync(filename, JSON.stringify(data, null, 2));
        console.log(`💾 Сохранено: ${filename}`);
    }
}

function main() {
    console.log('🧩 Sudoku Generator (Easy) (JavaScript)');
    const gen = new SudokuGenerator('easy');
    gen.generate();

    console.log('\nГоловоломка:');
    gen.printBoard();

    const readline = require('readline').createInterface({
        input: process.stdin,
        output: process.stdout
    });
    readline.question('\nПоказать решение? (y/n): ', (ans) => {
        if (ans.toLowerCase() === 'y') {
            console.log('\nРешение:');
            gen.printBoard(true);
        }
        gen.saveTXT();
        gen.saveJSON();
        readline.close();
    });
}

if (require.main === module) main();
