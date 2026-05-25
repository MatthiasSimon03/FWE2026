(() => {
    const WINNING_LINES = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8],
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8],
        [0, 4, 8],
        [2, 4, 6],
    ];

    const STORAGE_KEY = 'tic-tac-toe-state-v1';

    function createInitialState() {
        return {
            board: Array(9).fill(''),
            currentPlayer: 'X',
            winner: null,
            draw: false,
            message: 'Spiel gestartet. Spieler X beginnt.',
        };
    }

    function sanitizeBoard(board) {
        const normalized = Array.isArray(board) ? board.slice(0, 9) : [];
        while (normalized.length < 9) {
            normalized.push('');
        }

        return normalized.map((value) => (value === 'X' || value === 'O' ? value : ''));
    }

    function findWinner(board) {
        const normalized = sanitizeBoard(board);

        for (const [a, b, c] of WINNING_LINES) {
            if (normalized[a] && normalized[a] === normalized[b] && normalized[b] === normalized[c]) {
                return normalized[a];
            }
        }

        return null;
    }

    function isDraw(board) {
        const normalized = sanitizeBoard(board);
        return findWinner(normalized) === null && normalized.every((cell) => cell !== '');
    }

    function applyMove(state, index) {
        const next = {
            ...createInitialState(),
            ...state,
            board: sanitizeBoard(state?.board),
        };

        if (next.winner || next.draw) {
            next.message = 'Das Spiel ist bereits beendet. Bitte starte ein neues Spiel.';
            return next;
        }

        if (!Number.isInteger(index) || index < 0 || index > 8) {
            next.message = 'Ungültiges Feld.';
            return next;
        }

        if (next.board[index]) {
            next.message = 'Dieses Feld ist bereits belegt.';
            return next;
        }

        const player = next.currentPlayer;
        next.board[index] = player;

        const winner = findWinner(next.board);
        if (winner) {
            next.winner = winner;
            next.draw = false;
            next.message = `Spieler ${winner} hat gewonnen!`;
            return next;
        }

        if (isDraw(next.board)) {
            next.draw = true;
            next.message = 'Unentschieden!';
            return next;
        }

        next.currentPlayer = player === 'X' ? 'O' : 'X';
        next.message = `Spieler ${next.currentPlayer} ist am Zug.`;
        return next;
    }

    function loadState() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) {
                return createInitialState();
            }

            const parsed = JSON.parse(raw);
            const state = {
                ...createInitialState(),
                ...parsed,
                board: sanitizeBoard(parsed?.board),
            };

            if (findWinner(state.board)) {
                state.winner = findWinner(state.board);
                state.draw = false;
                state.currentPlayer = state.winner;
                state.message = `Spieler ${state.winner} hat gewonnen!`;
            } else if (isDraw(state.board)) {
                state.draw = true;
                state.winner = null;
                state.message = 'Unentschieden!';
            }

            return state;
        } catch {
            return createInitialState();
        }
    }

    function saveState(state) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
        } catch {
            // Ignore storage failures and keep the game playable.
        }
    }

    function getBoardElement() {
        return document.getElementById('board');
    }

    function updateHeader(state) {
        const currentPlayerElement = document.getElementById('currentPlayer');
        const statusTextElement = document.getElementById('statusText');
        const resetButton = document.getElementById('resetBtn');
        const boardElement = getBoardElement();

        if (typeof document !== 'undefined' && document.body) {
            document.body.dataset.gameState = state.winner ? 'won' : state.draw ? 'draw' : 'playing';
        }

        if (boardElement) {
            boardElement.classList.toggle('is-finished', Boolean(state.winner || state.draw));
            boardElement.classList.toggle('is-win', Boolean(state.winner));
            boardElement.classList.toggle('is-draw', Boolean(state.draw));
        }

        if (currentPlayerElement) {
            currentPlayerElement.textContent = state.currentPlayer;
        }

        if (statusTextElement) {
            statusTextElement.textContent = state.message;
        }

        if (resetButton) {
            resetButton.textContent = state.winner || state.draw ? 'Nochmal spielen' : 'Neues Spiel';
        }
    }

    function createCellContent(value) {
        if (!value) {
            return '';
        }

        const mark = document.createElement('div');
        mark.className = `mark mark-${value.toLowerCase()}`;
        mark.textContent = value;
        return mark;
    }

    function renderBoard(state) {
        const boardElement = getBoardElement();
        if (!boardElement) {
            return;
        }

        boardElement.innerHTML = '';

        state.board.forEach((cell, index) => {
            const cellWrapper = document.createElement('div');
            cellWrapper.className = 'cell';
            cellWrapper.setAttribute('role', 'gridcell');

            if (cell) {
                cellWrapper.appendChild(createCellContent(cell));
            } else if (!state.winner && !state.draw) {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('aria-label', `Feld ${index + 1}`);
                button.addEventListener('click', () => {
                    state = applyMove(state, index);
                    saveState(state);
                    render();
                });
                cellWrapper.appendChild(button);
            }

            boardElement.appendChild(cellWrapper);
        });
    }

    function render() {
        state = loadState();
        updateHeader(state);
        renderBoard(state);
    }

    function resetGame() {
        state = createInitialState();
        saveState(state);
        render();
    }

    let state = createInitialState();

    document.addEventListener('DOMContentLoaded', () => {
        const resetButton = document.getElementById('resetBtn');
        if (resetButton) {
            resetButton.addEventListener('click', resetGame);
        }

        state = loadState();
        saveState(state);
        render();
    });

    const api = {};
    api.createInitialState = createInitialState;
    api.sanitizeBoard = sanitizeBoard;
    api.findWinner = findWinner;
    api.isDraw = isDraw;
    api.applyMove = applyMove;

    if (typeof window !== 'undefined') {
        window.TicTacToeGame = api;
    }

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = api;
    }
})();


