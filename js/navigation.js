// Render a toggle at the top right of the site for agenda vs calendar view
const toggleView = document.createElement('div');
toggleView.className = 'view-toggle';
toggleView.innerHTML = `
	<a href="/events/">Agenda View</a>
	<span>|</span>
	<a href="/events/calendar/">Calendar View</a>
`;
document.body.appendChild(toggleView);

// Add a click event listener to the toggle view links
toggleView.querySelectorAll('a').forEach(link => {
	link.addEventListener('click', function (event) {
		event.preventDefault();
		const path = this.getAttribute('href');
		window.location.href = path;
	});
});

// Render a button in the top left of the site with a link to suggest a new event
const suggestEventButton = document.createElement('div');
suggestEventButton.className = 'suggest-event';
suggestEventButton.innerHTML = `
	<a href="/events/suggest/" target="_blank">Suggest an Event</a>
`;
document.body.appendChild(suggestEventButton);

// Add a click event listener to the suggest event button
suggestEventButton.querySelector('a').addEventListener('click', function (event) {
	event.preventDefault();
	const path = this.getAttribute('href');
	window.open(path, '_self');
});

// House Emoji to navigate home at the center of the top of the site
const homeButton = document.createElement('div');
homeButton.className = 'home-button';
homeButton.innerHTML = `
	<a href="/events/">
		<span class="home-emoji">🏠</span>
	</a>
`;
document.body.appendChild(homeButton);

// Fetcch the new suggestions from logs/event_suggestions.json
const fetchSuggestions = async () => {
	const response = await fetch('/logs/event_suggestions.json');
	const data = await response.json();
	return data;
};

// Render a page emoji with the count of new suggestions at the top of the site
const suggestions = await fetchSuggestions();
const suggestionCount = suggestions.length;
const suggestionCountElement = document.createElement('div');
suggestionCountElement.className = 'suggestion-count';
suggestionCountElement.innerHTML = `
	<span class="suggestion-emoji">📝</span>
	<span class="suggestion-count-number">${suggestionCount}</span>
`;
document.body.appendChild(suggestionCountElement);
