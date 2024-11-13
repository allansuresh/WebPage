import React from 'react';
import MainContent from './components/MainContent';
import './index.css';
import Disclaimer from './components/Disclaimer';

function App() {
  return (
    <div className="glassmorphism p-6 min-h-screen bg-gradient-to-r from-blue-100 to-blue-50">
      <header className="py-16 text-center">
        <h1 className="text-6xl font-extrabold text-blue-700 tracking-wide">Magical Story Generator</h1>
        <p className="text-lg text-gray-700 mt-4">Craft your own Harry Potter fan-fiction in seconds.</p>
      </header>
      <MainContent />
      <Disclaimer />
    </div>
  );
}

export default App;
