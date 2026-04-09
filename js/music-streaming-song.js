// Bật-Tắt nhạc bằng nút Play bự & Mở thanh phát nhạc
document.getElementById("playBtn").addEventListener("click", openSongPopUp);
var turnOnMusic = 0; // Trạng thái đã bật nhạc hay chưa
function openSongPopUp() {
    document.getElementById("popup").style.display = "block";
    if (document.getElementById("playBtn").textContent === "⏵ PLAY") {
        document.getElementById("playBtn").textContent = "⏸ PAUSE"
        document.getElementById("playImg").src = "./img/pause.png";
        document.getElementById("myAudio").play();
        turnOnMusic = 1; // Đã bật nhạc
    } else {
        document.getElementById("playBtn").textContent = "⏵ PLAY"
        document.getElementById("playImg").src = "./img/play-music.png";
        document.getElementById("myAudio").pause();
        turnOnMusic = 0; // Đã tắt nhạc
    }
};
// Bật-Tắt nhặc bằng nút Play nhỏ
document.getElementById("play").addEventListener("click", playMusic);
function playMusic() {
    if (document.getElementById("playBtn").textContent === "⏸ PAUSE") {
        document.getElementById("playBtn").textContent = "⏵ PLAY"
        document.getElementById("playImg").src = "./img/play-music.png";
        document.getElementById("myAudio").pause();
        turnOnMusic = 0; // Đã tắt nhạc
    } else {
        document.getElementById("playBtn").textContent = "⏸ PAUSE"
        document.getElementById("playImg").src = "./img/pause.png";
        document.getElementById("myAudio").play();
        turnOnMusic = 1; // Đã bật nhạc
    }
}
// Trạng thái đã thích bài hát hay chưa
document.getElementById("likeBtn").addEventListener("click", likeSong);
var like = 0;
function likeSong() {
    if (like == 0) {
        document.getElementById("likeBtn").textContent = "♥ Liked";
        document.getElementById("likeBtn").classList.add("liked");
        like = 1;
    } else {
        document.getElementById("likeBtn").textContent = "♥ Like";
        document.getElementById("likeBtn").classList.remove("liked");
        like = 0;
    }
}
let audio = document.querySelector('#myAudio');
let currentDuration = document.getElementById('currentDuration');
let durationSlide = document.querySelector('#durationRange');
// Thanh âm lượng
document.getElementById("volumeRange").addEventListener("input", modifyVolume);
function modifyVolume() {
   var newVolume = document.getElementById('volumeRange').value;
   audio.volume = newVolume;
}
setInterval(range_slider, 1000);
function getDuration(seconds) {
    var audioDuration = audio.duration;
    var minute, second;
    minute = Math.floor(audioDuration);
    second = Math.floor((audioDuration - minute) * 60);
    return minute * 60 + second;
}
// Đổi thời lượng hiện trên thanh phát nhạc lúc nhạc đang phát
durationRange.addEventListener("change", changeDuration);
function changeDuration() {
    durationRange = audio.duration * (durationSlide.value / 100);
    audio.currentTime = durationRange;
}
// Thể hiện thời lượng nhạc trên thanh phát nhạc
function formatDuration(time) {
    var minute, second;
    minute = Math.floor(time / 60);
    second = Math.floor(time - minute * 60);
    var minuteStr = minute;
    var secondStr = second;
    if (minute < 10) { minuteStr = '0' + minuteStr; } 
    if (second < 10) { secondStr = '0' + secondStr; }
    return minuteStr + ':' + secondStr;
}
function range_slider() {
    let position = 0;
    if (!isNaN(audio.duration)) {
        position = audio.currentTime * (100 / audio.duration);
        durationSlide.value = position;
        currentDuration.innerHTML = formatDuration(audio.currentTime);
        document.getElementById('endDuration').innerHTML = formatDuration(audio.duration);
        //alert(audio.duration);
    }
    if (audio.ended) {
        durationSlide.value = 0;
        currentDuration.innerHTML = formatDuration(0);
        document.getElementById("playBtn").textContent = "⏵ PLAY"
        document.getElementById("playImg").src = "./img/play-music.png";
    }
}
document.getElementById("add_to_playlist_btn").addEventListener("click", openAddPlaylistPopUp);
function openAddPlaylistPopUp() {
    document.getElementById("add_to_playlist_popup").style.display = "block";
}
document.getElementById("popupClose").addEventListener("click", closeAddPlaylistPopUp);
function closeAddPlaylistPopUp(){
    document.getElementById("add_to_playlist_popup").style.display = "none";
}
document.getElementById("create_new_playlist").addEventListener("click", openPlaylistPopUp);
function openPlaylistPopUp() {
    document.getElementById("add_to_playlist_popup").style.display = "none";
    document.getElementById("add_playlist_popup").style.display = "block";
}
document.getElementById("popup_close").addEventListener("click", closePlaylistPopup);
function closePlaylistPopup() {
    document.getElementById("add_to_playlist_popup").style.display = "block";
    document.getElementById("add_playlist_popup").style.display = "none";
}