document.addEventListener('DOMContentLoaded', () => {
  // Открытие/закрытие модального окна
  document.querySelectorAll('.btn-gph').forEach((button) => {
    button.addEventListener('click', (e) => {
      document.querySelector(`#${e.target.dataset.target}`).classList.add('active');
    });

    document.querySelector(`#${button.dataset.target}`).addEventListener('click', (e) => {
      e.target.classList.remove('active');
    });
  });

  // Меню при скролле
  const header = document.querySelector('header');
  const headerMobile = header.cloneNode(true);

  header.before(headerMobile);
  headerMobile.classList.add('header-mobile');
  headerMobile.style.top = `-${header.clientHeight}px`;
  window.onscroll = function () {
    const margin = document.querySelector('.main-screen').clientHeight / 2;
    // debugger;
    if (document.body.scrollTop > margin || document.documentElement.scrollTop > margin) {
      headerMobile.style.top = '0';
    } else {
      headerMobile.style.top = `-${header.clientHeight}px`;
    }
  };
});
