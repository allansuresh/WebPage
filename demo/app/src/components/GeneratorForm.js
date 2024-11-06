// src/components/GeneratorForm.js
import React, { useState } from 'react';
import StoryOutput from './StoryOutput';

function GeneratorForm() {
  const [startPhrase, setStartPhrase] = useState('harry potter');
  const [wordLimit, setWordLimit] = useState(100);
  const [story, setStory] = useState('Your magical story will appear here...');
  const [loading, setLoading] = useState(false);

  const handleGenerate = async () => {
    setLoading(true);
    setStory('');

    try {
      const response = await fetch('http://localhost:8000/demo/generate.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          start: startPhrase,
          limit: wordLimit,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setStory(data.story);
      } else {
        setStory('Error generating story. Please try again.');
      }
    } catch (error) {
      console.error('Error:', error);
      setStory('An error occurred while generating the story.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      {/* Input Section */}
      <div className="space-y-6 mb-8">
        <div>
          <label className="block text-sm font-medium text-gray-200 mb-2">Choose a Starting Phrase</label>
          <select
            value={startPhrase}
            onChange={(e) => setStartPhrase(e.target.value)}
            className="w-full rounded-lg bg-gray-700 text-gray-200 border-gray-600 focus:border-gold-500 focus:ring-gold-500 py-3"
          >
            {/* Options */}
            <option value="harry potter">Harry Potter</option>
            <option value="hermione granger">Hermione Granger</option>
            <option value="ron weasley">Ron Weasley</option>
            <option value="dumbledore looked">Dumbledore looked</option>
            <option value="hogwarts was">Hogwarts was</option>
            <option value="the golden trio">The Golden Trio</option>
            <option value="in the common">In the common</option>
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-200 mb-2">Story Length</label>
          <div className="flex gap-4">
            {[
              { label: 'Short', value: 50 },
              { label: 'Medium', value: 100 },
              { label: 'Long', value: 150 },
            ].map((option) => (
              <button
                key={option.value}
                onClick={() => setWordLimit(option.value)}
                className={`flex-1 py-3 rounded-lg ${
                  wordLimit === option.value ? 'bg-gold-500 text-gray-900' : 'bg-gray-700 text-gray-200'
                } hover:bg-gold-500 hover:text-gray-900 transition`}
              >
                {option.label}
              </button>
            ))}
          </div>
        </div>

        <button
          onClick={handleGenerate}
          disabled={loading}
          className="w-full bg-gold-500 text-gray-900 px-6 py-3 rounded-lg hover:bg-gold-600 transition-colors flex items-center justify-center gap-2"
        >
          <span>{loading ? 'Generating...' : 'Generate Story'}</span>
          {loading && (
            <svg className="animate-spin h-5 w-5 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
              <path className="opacity-75" fill="currentColor" d="M12 2v6m0 8v6m6-6h6M6 12H0"></path>
            </svg>
          )}
        </button>
      </div>

      {/* Output Section */}
      <StoryOutput story={story} />
    </>
  );
}

export default GeneratorForm;
