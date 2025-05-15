// Create the navigation bar container
const navigationBar = document.createElement('div');
navigationBar.className = 'navigation-bar';

// Define emoji and text content for responsive switching
const navContent = {
	suggestEvent: {
		text: 'Suggest an Event',
		emoji: '➕'
	},
	agendaView: {
		text: 'Agenda View',
		emoji: '📋'
	},
	calendarView: {
		text: 'Calendar View',
		emoji: '📅'
	}
};

// Create the suggest event button
const suggestEventButton = document.createElement('div');
suggestEventButton.className = 'suggest-event';
suggestEventButton.innerHTML = `
    <a href="/events/suggest/" class="text-link">${navContent.suggestEvent.text}</a>
    <a href="/events/suggest/" class="emoji-link" style="display:none;">${navContent.suggestEvent.emoji}</a>
`;

// Create initial suggestion count element (will be updated after fetch)
const suggestionCountElement = document.createElement('div');
suggestionCountElement.className = 'suggestion-count';
suggestionCountElement.innerHTML = `
    <span class="suggestion-emoji">📝</span>
    <span class="suggestion-count-number">0</span>
`;

// Create home button
const homeButton = document.createElement('div');
homeButton.className = 'home-button';
homeButton.innerHTML = `
    <a href="/events/">
        <span class="home-emoji">🏠</span>
    </a>
`;

// Create view toggle
const toggleView = document.createElement('div');
toggleView.className = 'view-toggle';
toggleView.innerHTML = `
    <a href="/events/" class="text-link">${navContent.agendaView.text}</a>
    <a href="/events/" class="emoji-link" style="display:none;">${navContent.agendaView.emoji}</a>
    <span class="separator">|</span>
    <a href="/events/calendar/" class="text-link">${navContent.calendarView.text}</a>
    <a href="/events/calendar/" class="emoji-link" style="display:none;">${navContent.calendarView.emoji}</a>
`;

// Add all elements to the navigation bar in the specified order
navigationBar.appendChild(suggestEventButton);
navigationBar.appendChild(suggestionCountElement);
navigationBar.appendChild(homeButton);
navigationBar.appendChild(toggleView);

// Add the navigation bar to the document body
document.body.insertBefore(navigationBar, document.body.firstChild);

// Function to update navigation display based on screen size
function updateNavigationDisplay() {
	const isMobile = window.matchMedia('(max-width: 768px)').matches;

	// Get all text and emoji links
	const textLinks = navigationBar.querySelectorAll('.text-link');
	const emojiLinks = navigationBar.querySelectorAll('.emoji-link');
	const separators = navigationBar.querySelectorAll('.separator');

	// On mobile, hide text and show emojis
	if (isMobile) {
		textLinks.forEach(link => link.style.display = 'none');
		emojiLinks.forEach(link => link.style.display = 'inline-block');
		separators.forEach(sep => sep.style.display = 'none');

		// Add tooltip attributes on mobile for accessibility
		const suggestEmoji = suggestEventButton.querySelector('.emoji-link');
		const agendaEmoji = toggleView.querySelector('.emoji-link:first-of-type');
		const calendarEmoji = toggleView.querySelector('.emoji-link:last-of-type');

		if (suggestEmoji) suggestEmoji.title = navContent.suggestEvent.text;
		if (agendaEmoji) agendaEmoji.title = navContent.agendaView.text;
		if (calendarEmoji) calendarEmoji.title = navContent.calendarView.text;
	} else {
		// On desktop, show text and hide emojis
		textLinks.forEach(link => link.style.display = 'inline');
		emojiLinks.forEach(link => link.style.display = 'none');
		separators.forEach(sep => sep.style.display = 'inline');
	}
}

// Call the function initially
updateNavigationDisplay();

// Add window resize listener to update navigation on screen size change
window.addEventListener('resize', updateNavigationDisplay);

// Add click event listeners to all navigation links
navigationBar.querySelectorAll('a').forEach(link => {
	link.addEventListener('click', function (event) {
		event.preventDefault();
		const path = this.getAttribute('href');
		window.location.href = path;
	});
});

// Add click event to suggestion count to navigate to suggestions page
suggestionCountElement.addEventListener('click', () => {
	window.location.href = '/events/suggest/';
});

// Fetch the new suggestions from logs/event_suggestions.json
const fetchSuggestions = async () => {
	try {
		const response = await fetch('./logs/event_suggestions.json');
		if (!response.ok) {
			throw new Error(`Failed to fetch suggestions: ${response.status}`);
		}
		const data = await response.json();
		return data;
	} catch (error) {
		console.error('Error fetching suggestions:', error);
		return []; // Return empty array on error to prevent downstream failures
	}
};

// Update the suggestion count display
const updateSuggestionCount = async () => {
	try {
		const suggestions = await fetchSuggestions();
		const suggestionCount = suggestions.length;
		const countElement = suggestionCountElement.querySelector('.suggestion-count-number');
		countElement.textContent = suggestionCount;
	} catch (error) {
		console.error('Error updating suggestion count:', error);
	}
};

// Call the function to update the suggestion count
updateSuggestionCount();
