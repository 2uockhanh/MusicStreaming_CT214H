const ThemeHandler = {
    themeKey: "emuzik_theme",

    init: function() {
        const savedTheme = CookieHandler.get(this.themeKey) || "dark";
        this.apply(savedTheme);
    },

    apply: function(theme) {
        if (theme === "light") {
            document.documentElement.classList.add("light-mode");
        } else {
            document.documentElement.classList.remove("light-mode");
        }
    },

    toggle: function(mode) {
        CookieHandler.set(this.themeKey, mode);
        this.apply(mode);

        const dropdown = document.querySelector('.dropdown-menu');
        if (dropdown) {
            dropdown.style.display = 'none'; 
            setTimeout(() => dropdown.style.display = '', 500);
        }
    }
};

ThemeHandler.init();