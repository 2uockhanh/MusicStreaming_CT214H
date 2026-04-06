document.getElementById("dropbtn").addEventListener("click", openAvatarBlock);
function openAvatarBlock() {
    document.getElementById("dropDown").classList.toggle("showDropDown");
};

document.getElementById("add_playlist_btn").addEventListener("click", openCreatePlaylistPopup);
function openCreatePlaylistPopup() {
    document.getElementById("add_playlist_popup").style.display = "block";
};

document.getElementById("popup_close").addEventListener("click", closePopup);
function closePopup() {
    document.getElementById("add_playlist_popup").style.display = "none";
};

document.getElementById("changeSeenMode").addEventListener("click", changeSeenMode);
function changeSeenMode() {
    if (document.getElementById("changeSeenMode").textContent === "🔒 Private") {
        document.getElementById("changeSeenMode").textContent = "🔓 Public";
    } else {
        document.getElementById("changeSeenMode").textContent = "🔒 Private";
    }
};