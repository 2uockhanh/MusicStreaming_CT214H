document.getElementById("playBtn").addEventListener("click", openSongPopUp);
function openSongPopUp() {
    document.getElementById("popup").style.display = "block";
    document.getElementById("playBtn").textContent = "⏸ PAUSE"
    document.getElementById("playImg").src = "./img/pause.png";
    playMusic();
};
function playMusic() {
    document.getElementById("myAudio").play();
}