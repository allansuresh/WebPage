// src/App.js
import React from 'react';
import MainContent from './components/MainContent';

function App() {
  return (
    <div
      className="min-h-screen bg-cover bg-center"
      style={{ backgroundImage: "url('/images/magic-bg.jpg')" }}
    >
      <div className="bg-black bg-opacity-60 min-h-screen">
        <MainContent />
      </div>
    </div>
  );
}

export default App;
