document.getElementById("dropbtn").addEventListener("click", openAvatarBlock);
function openAvatarBlock() {
    document.getElementById("dropDown").classList.toggle("showDropDown");
};

document.getElementById("themeMode").addEventListener("click", changeModeToggle);
function changeModeToggle() {	
	if (document.getElementById("themeMode").textContent === "🌙 Theme Mode") {
		document.getElementById("themeMode").textContent = "☀️ Theme Mode";
		document.body.classList.add("light-body");
	} else {
		document.getElementById("themeMode").textContent = "🌙 Theme Mode";
		document.body.classList.remove("light-body");
	}
};