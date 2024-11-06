// src/components/MainContent.js
import React from 'react';
import GeneratorForm from './GeneratorForm';

function MainContent() {
  return (
    <main className="container mx-auto pt-32 pb-12 px-4">
      <div className="text-center mb-12">
        <h1 className="text-5xl font-extrabold text-gold-500 mb-6 drop-shadow-lg">
          Harry Potter Fan-Fiction Generator
        </h1>
        <p className="text-xl text-gray-200 max-w-2xl mx-auto">
          Craft your own magical stories with our enchanted generator.
        </p>
      </div>
      <div className="bg-gray-800 bg-opacity-75 rounded-2xl shadow-lg p-8">
        <GeneratorForm />
      </div>
    </main>
  );
}

export default MainContent;
