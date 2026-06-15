define([], function() {
    return {
        init: function(id) {
            console.log('amddddddd');

            ["ssh", "https", "checkout"].forEach((name) => {
                const prefix = `#code-dropdown-${id}`;
                const input = document.querySelector(`${prefix} .${name}-input`);
                const button = document.querySelector(`${prefix} .${name}-button`);
                const icon = document.querySelector(`${prefix} .${name}-button i`);
                if (!input || !button || !icon) {
                    return;
                }

                button.addEventListener("click", () => {
                    navigator.clipboard.writeText(input.value).then(() => {
                        icon.classList.replace("fa-copy", "fa-check");
                        setTimeout(() => icon.classList.replace("fa-check", "fa-copy"), 500);
                    });
                });
            });
        },
    };
});
