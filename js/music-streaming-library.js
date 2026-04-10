document.addEventListener('DOMContentLoaded', function() {
    console.log('Library JS loaded');
    const dropbtn = document.getElementById("dropbtn");
    if (dropbtn) {
        dropbtn.addEventListener("click", openAvatarBlock);
        console.log('Dropbtn event listener added');
    } else {
        console.error('Dropbtn not found');
    }
});

function openAvatarBlock() {
    console.log('openAvatarBlock called');
    const dropdown = document.getElementById("dropDown");
    if (dropdown) {
        dropdown.classList.toggle("showDropDown");
        console.log('Dropdown toggled, current classes:', dropdown.className);
    } else {
        console.error('Dropdown not found');
    }
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