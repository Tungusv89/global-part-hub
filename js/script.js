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

    if (document.body.scrollTop > margin || document.documentElement.scrollTop > margin) {
      headerMobile.style.top = '0';
    } else {
      headerMobile.style.top = `-${header.clientHeight}px`;
    }
  };
  console.log(window.location.search);
  // Ajax на форме
  document.querySelectorAll('form').forEach((form) => {

    // Устанавливаем событие отправки для формы
    form.addEventListener('submit', function (event) {
      const button = form.querySelector('button[type="submit"]');
      button.classList.add('sending');
      button.querySelectorAll('input').forEach((input) => {
        input.disabled;
      });
      // Отменяем стандартное поведение формы
      event.preventDefault();
      // Собираем все данные из формы
      const form_data = new FormData(form);
      // Создаем объект XMLHttpRequest для асинхронного запроса
      const xhr = new XMLHttpRequest();
      // debugger;
      let request_url = 'form.php' + window.location.search;

      // Настраиваем запрос
      xhr.open('POST',  request_url); // Метод и путь до php файла отправителя
      // Устанавливаем функцию, которая выполняется при успешной отправке сообщения
      xhr.onload = function () {
        if (xhr.status === 200) {
          // Код в этом блоке выполняется при успешной отправке сообщения
          console.log('Ваше сообщение отправлено!');
        }
      };
      // Отправляем запрос с данными формы
      xhr.send(form_data);
      button.querySelectorAll('input').forEach((input) => {
        input.removeAttribute('disabled');
      });
      button.classList.remove('sending');
    });
  });
});
