const assert = require('assert');
const game = require('../../public/game.js');

function testInitialState() {
    const state = game.createInitialState();
    assert.strictEqual(state.board.length, 9);
    assert.deepStrictEqual(state.board, Array(9).fill(''));
    assert.strictEqual(state.currentPlayer, 'X');
    assert.strictEqual(state.winner, null);
    assert.strictEqual(state.draw, false);
}

function testWinnerDetection() {
    assert.strictEqual(game.findWinner(['X', 'X', 'X', '', '', '', '', '', '']), 'X');
    assert.strictEqual(game.findWinner(['O', '', '', 'O', '', '', 'O', '', '']), 'O');
    assert.strictEqual(game.findWinner(['X', 'O', 'X', 'O', 'X', 'O', 'O', 'X', 'X']), 'X');
}

function testDrawDetection() {
    assert.strictEqual(game.isDraw(['X', 'O', 'X', 'X', 'O', 'O', 'O', 'X', 'X']), true);
    assert.strictEqual(game.isDraw(['X', 'X', 'X', 'O', 'O', '', '', '', '']), false);
}

function testMoveFlow() {
    let state = game.createInitialState();
    state = game.applyMove(state, 0);
    assert.strictEqual(state.board[0], 'X');
    assert.strictEqual(state.currentPlayer, 'O');

    state = game.applyMove(state, 1);
    assert.strictEqual(state.board[1], 'O');
    assert.strictEqual(state.currentPlayer, 'X');

    const occupied = game.applyMove(state, 0);
    assert.strictEqual(occupied.message, 'Dieses Feld ist bereits belegt.');
}

function run() {
    testInitialState();
    testWinnerDetection();
    testDrawDetection();
    testMoveFlow();
    console.log('All Tic Tac Toe tests passed.');
}

run();


