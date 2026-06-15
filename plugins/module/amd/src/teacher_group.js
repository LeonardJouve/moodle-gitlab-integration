function copy(text) {
    if (navigator.clipboard) {
        return navigator.clipboard.writeText(text);
    }

    const textarea = document.createElement("textarea");
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length)
    document.execCommand("copy");
    document.body.removeChild(textarea);
}

define([], function() {
    return {
        init: function(id) {
            ["ssh", "https", "checkout"].forEach((name) => {
                const prefix = `#code-dropdown-${id}`;
                const input = document.querySelector(`${prefix} .${name}-input`);
                const button = document.querySelector(`${prefix} .${name}-button`);
                const icon = document.querySelector(`${prefix} .${name}-button i`);
                if (!input || !button || !icon) {
                    return;
                }

                button.addEventListener("click", () => {
                    copy(input.value).then(() => {
                        icon.classList.replace("fa-copy", "fa-check");
                        setTimeout(() => icon.classList.replace("fa-check", "fa-copy"), 500);
                    });
                });
            });
        },
    };
});
