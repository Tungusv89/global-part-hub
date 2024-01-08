document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.btn-gph').forEach((button) => {
    button.addEventListener('click', (e) => {
      document.querySelector(`#${e.target.dataset.target}`).classList.add('active');
    });

    document.querySelector(`#${button.dataset.target}`).addEventListener('click', (e) => {
      e.target.classList.remove('active');
    });
  });
});
