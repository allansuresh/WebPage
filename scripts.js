// Global variable to store the current word limit
let currentWordLimit = 100;

// Function to set word limit from buttons
function setWordLimit(limit) {
    currentWordLimit = limit;
    // Update UI to show which button is selected
    const buttons = document.querySelectorAll('[onclick^="setWordLimit"]');
    buttons.forEach(button => {
        if (parseInt(button.getAttribute('onclick').match(/\d+/)[0]) === limit) {
            button.classList.add('bg-blue-50', 'border-blue-500');
        } else {
            button.classList.remove('bg-blue-50', 'border-blue-500');
        }
    });
}

// Function to show/hide loading state
function setLoading(isLoading) {
    const generateBtn = document.getElementById('generateBtn');
    const loadingIcon = document.getElementById('loadingIcon');
    const btnText = generateBtn.querySelector('span');

    if (isLoading) {
        generateBtn.disabled = true;
        generateBtn.classList.add('opacity-75');
        loadingIcon.classList.remove('hidden');
        btnText.textContent = 'Generating...';
    } else {
        generateBtn.disabled = false;
        generateBtn.classList.remove('opacity-75');
        loadingIcon.classList.add('hidden');
        btnText.textContent = 'Generate Story';
    }
}

// Function to update the story output
function updateStoryOutput(story, error = false) {
    const storyOutput = document.getElementById('storyOutput');
    storyOutput.textContent = story;
    
    if (error) {
        storyOutput.classList.add('text-red-600');
    } else {
        storyOutput.classList.remove('text-red-600');
    }
}

// Function to generate the story
async function generateStory() {
    const startPhrase = document.getElementById('startPhrase').value;
    
    setLoading(true);
    
    try {
        const response = await fetch('http://localhost:8000/demo/generate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                start: startPhrase,
                limit: currentWordLimit
            })
        });

        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Failed to generate story');
        }

        updateStoryOutput(data.story);
    } catch (error) {
        console.error('Error:', error);
        updateStoryOutput(`Error generating story: ${error.message}`, true);
    } finally {
        setLoading(false);
    }
}

// Add event listeners when the document loads
document.addEventListener('DOMContentLoaded', function() {
    // Set initial word limit
    setWordLimit(100);
    
    // Add click handler to generate button
    const generateBtn = document.getElementById('generateBtn');
    generateBtn.addEventListener('click', generateStory);
});