document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.collapsible-toggle');

    toggles.forEach(toggle => {
      toggle.addEventListener('click', function () {
        this.classList.toggle('active');
        const content = this.nextElementSibling;

        if (this.classList.contains('active')) {
          content.style.maxHeight = content.scrollHeight + "px";
        } else {
          content.style.maxHeight = "0";
        }
      });
    });
  });

  window.addEventListener("resize", () => {
  document.querySelectorAll('.collapsible-toggle.active').forEach(toggle => {
    const content = toggle.nextElementSibling;
    content.style.maxHeight = content.scrollHeight + "px";
  });
});
