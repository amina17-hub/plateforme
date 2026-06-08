import React from 'react';
import ReactDOM from 'react-dom/client';

function App() {
  return <h1>Bienvenue sur la plateforme des artisans !</h1>;
}

ReactDOM.createRoot(document.getElementById('app')).render(<App />);
document.addEventListener('DOMContentLoaded', function() {
  let btn = document.getElementById('btn-login');
  let modal = document.getElementById('loginModal');
  let close = document.querySelector('.close');

  btn.addEventListener('click', function() {
      modal.style.display = 'flex';
  });

  close.addEventListener('click', function() {
      modal.style.display = 'none';
  });
});
