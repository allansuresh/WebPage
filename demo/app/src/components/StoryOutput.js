// src/components/StoryOutput.js
import React from 'react';

function StoryOutput({ story }) {
  return (
    <div>
      <label className="block text-sm font-medium text-gray-200 mb-2">Generated Story</label>
      <div
        className="bg-gray-800 bg-opacity-50 rounded-lg p-6 min-h-[200px] text-gray-100 whitespace-pre-wrap border border-gray-600 overflow-y-auto max-h-96"
      >
        {story}
      </div>
    </div>
  );
}

export default StoryOutput;
