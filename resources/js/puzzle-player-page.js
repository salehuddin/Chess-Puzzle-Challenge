import puzzlePlayer from './puzzle-player';
import 'chessground/assets/chessground.base.css';
import 'chessground/assets/chessground.brown.css';
import 'chessground/assets/chessground.cburnett.css';

window.puzzlePlayer = puzzlePlayer;

if (window.Alpine) {
    window.Alpine.data('puzzlePlayer', puzzlePlayer);
} else {
    document.addEventListener('alpine:init', () => {
        window.Alpine.data('puzzlePlayer', puzzlePlayer);
    });
}
