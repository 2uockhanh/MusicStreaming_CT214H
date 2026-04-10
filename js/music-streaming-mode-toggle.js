var bd = document.body;
var btn = document.getElementById("darkModeToggle");
btn.addEventListener("click", changeModeToggle);
function changeModeToggle() {	
	bd.classList.toggle("dark-body");
	btn.classList.toggle("dark-button");
	if (btn.textContent === "🌙 DARK") {
		btn.textContent = "☀️ LIGHT";
		btn.classList.add("light-button");
		bd.classList.add("light-body");
	} else {
		btn.textContent = "🌙 DARK";
		btn.classList.remove("light-button");
		bd.classList.remove("light-body");
	}
};