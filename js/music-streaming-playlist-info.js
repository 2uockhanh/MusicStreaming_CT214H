document.getElementById("addSongsBtn").addEventListener("click", openAddSongsPopup);
function openAddSongsPopup() {
    document.getElementById("add_songs_popup").style.display = "block";
}

document.getElementById("popupClose").addEventListener("click", closeAddSongsPopup);
function closeAddSongsPopup() {
    document.getElementById("add_songs_popup").style.display = "none";
}

document.getElementById("editPlaylistBtn").addEventListener("click", openEditPlaylistPopup);
function openEditPlaylistPopup() {
    document.getElementById("edit_playlist_popup").style.display = "block";
}
document.getElementById("popup_close").addEventListener("click", closeEditPlaylistPopup);
function closeEditPlaylistPopup() {
    document.getElementById("edit_playlist_popup").style.display = "none";
}
document.getElementById("changeSeenMode").addEventListener("click", changeSeenMode);
function changeSeenMode() {
    if (document.getElementById("changeSeenMode").textContent === "🔒 Private") {
        document.getElementById("changeSeenMode").textContent = "🔓 Public";
    } else {
        document.getElementById("changeSeenMode").textContent = "🔒 Private";
    }
};