document.getElementById("btn_login").addEventListener("click", checkLogInInput);
function checkLogInInput() {
	const username = document.getElementById("input_username").value;
	const password = document.getElementById("input_password").value;
	if (username === "" || password === "") {
		alert("Please type your information.");
		return;
	}
    // Mật khẩu không hợp lệ
	if (!validPassword(password)) {
		alert("Your password is invalid.");
		return;
	}
	// Tất cả thông tin hợp lệ
	alert("Your information is valid.");
}
function validPassword(inputPassword) {
	// Độ dài mật khẩu có ít nhất 8 ký tự
	if (inputPassword.length < 8) {
    	alert("Your password must have at least 8 characters.");
		return false;
	}
	// Các ký tự đặc biệt được phép dùng
	const specialCharList = '!@#$%^&*()-_+=[]{}:;><?|~,.';
	// Các biến đếm số ký tự thường, hoa, đặc biệt, chữ số
	let lowerChar = 0, upperChar = 0, specialChar = 0, digitChar = 0;
	for (let i = 0; i < inputPassword.length; i++) {
		// Mật khẩu không bao gồm ký tự cách
		if (inputPassword.charAt(i) == ' ') { return false; }
		if (inputPassword.charAt(i) >= 'A' && inputPassword.charAt(i) <= 'Z') { upperChar++; }
		if (inputPassword.charAt(i) >= 'a' && inputPassword.charAt(i) <= 'z') { lowerChar++; }
		if (inputPassword.charAt(i) >= '0' && inputPassword.charAt(i) <= '9') { digitChar++; }
		if (specialCharList.includes(inputPassword.charAt(i))) { specialChar++; }
	}
	// Mật khẩu cần phải có ít nhất 1 ký tự thường, 1 ký tự hoa, 1 ký tự đặc biệt, 1 ký tự chữ số
	if (lowerChar == 0 || upperChar == 0 || specialChar == 0 || digitChar == 0) {
		return false;
	}
	return true;
}