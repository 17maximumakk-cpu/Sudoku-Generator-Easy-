# sudoku_generator.rb — Ruby версия

class SudokuGenerator
  attr_reader :board, :solution

  def initialize(difficulty = 'easy')
    @size = 9
    @box_size = 3
    @board = Array.new(@size) { Array.new(@size, 0) }
    @solution = Array.new(@size) { Array.new(@size, 0) }
    @cells_to_remove = difficulty == 'easy' ? 35 : difficulty == 'medium' ? 45 : 55
  end

  def generate
    solve(@board)
    @size.times do |i|
      @size.times do |j|
        @solution[i][j] = @board[i][j]
      end
    end
    remove_cells
  end

  def solve(board)
    empty = find_empty(board)
    return true if empty.nil?
    row, col = empty
    (1..9).to_a.shuffle.each do |num|
      if valid?(board, row, col, num)
        board[row][col] = num
        return true if solve(board)
        board[row][col] = 0
      end
    end
    false
  end

  def find_empty(board)
    @size.times do |i|
      @size.times do |j|
        return [i, j] if board[i][j] == 0
      end
    end
    nil
  end

  def valid?(board, row, col, num)
    @size.times do |j|
      return false if board[row][j] == num
    end
    @size.times do |i|
      return false if board[i][col] == num
    end
    start_row = (row / @box_size) * @box_size
    start_col = (col / @box_size) * @box_size
    start_row.upto(start_row + @box_size - 1) do |i|
      start_col.upto(start_col + @box_size - 1) do |j|
        return false if board[i][j] == num
      end
    end
    true
  end

  def remove_cells
    cells = []
    @size.times { |i| @size.times { |j| cells << [i, j] } }
    cells.shuffle!
    removed = 0
    cells.each do |row, col|
      break if removed >= @cells_to_remove
      @board[row][col] = 0
      removed += 1
    end
  end

  def print_board(show_solution = false)
    board_to_print = show_solution ? @solution : @board
    puts "┌───────┬───────┬───────┐"
    @size.times do |i|
      print "│"
      @size.times do |j|
        if board_to_print[i][j] == 0
          print " . "
        else
          print " #{board_to_print[i][j]} "
        end
        print "│" if j % 3 == 2 && j < @size - 1
      end
      puts "│"
      puts "├───────┼───────┼───────┤" if i % 3 == 2 && i < @size - 1
    end
    puts "└───────┴───────┴───────┘"
  end

  def save_txt(filename = 'sudoku_puzzle.txt')
    File.open(filename, 'w') do |f|
      @board.each do |row|
        f.puts row.map { |c| c == 0 ? '.' : c }.join(' ')
      end
    end
    puts "💾 Сохранено: #{filename}"
  end

  def save_json(filename = 'sudoku_puzzle.json')
    data = { puzzle: @board, solution: @solution, difficulty: 'easy' }
    File.write(filename, JSON.pretty_generate(data))
    puts "💾 Сохранено: #{filename}"
  end
end

def main
  puts "🧩 Sudoku Generator (Easy) (Ruby)"
  gen = SudokuGenerator.new('easy')
  gen.generate

  puts "\nГоловоломка:"
  gen.print_board

  print "\nПоказать решение? (y/n): "
  if gets.chomp.downcase == 'y'
    puts "\nРешение:"
    gen.print_board(true)
  end

  gen.save_txt
  gen.save_json
end

main if __FILE__ == $0
