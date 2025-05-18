<!DOCTYPE html>
<html lang="en">
<head>
	<base href="/events/">
    
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Event Details</title>
	<link rel="stylesheet" href="/events/css/EventsList.css">
	<!-- Share tags -->
<?php
	/* Get the event detuls from the JSON file */
	$events = json_decode(file_get_contents('events.json'), true);
	$event = null;
	if (isset($_GET['id'])) {
		// search for the event in the JSON file
		$name = $_GET['name'];
		$date = $_GET['date'];
		$id = $_GET['id'];
		foreach ($events as $key => $event) {
			if (isset($event['id']) && $event['id'] == $id) {
				break;
			}
		}
		// If the event is not found, set it to null
		if ($event === null) {
			$event = null;
		}

		// If there is no image
		if (empty($event['image'])) {
			// Generate an image using the event details
			$imageText = str_replace(' ', '_', $name) . '_' . str_replace('-', '_', $date);
			$imagePath = 'uploads/event_' . uniqid() . '.png';

			// Create event image using GD library
			$width = 1200;
			$height = 630;
			$img = imagecreatetruecolor($width, $height);

			// Define colors
			$background = imagecolorallocate($img, 41, 128, 185); // Blue background
			$textColor = imagecolorallocate($img, 255, 255, 255); // White text
			$accentColor = imagecolorallocate($img, 243, 156, 18); // Orange accent

			// Fill background
			imagefill($img, 0, 0, $background);

			// Add a decorative rectangle at the top
			imagefilledrectangle($img, 0, 0, $width, 100, $accentColor);

			// Load a font - make sure this path exists
			$fontPath = __DIR__ . '/fonts/arial.ttf';
			// If font doesn't exist, use default
			if (!file_exists($fontPath)) {
				$fontPath = 5; // Use built-in font
			}

			// Add event details to the image
			if (is_numeric($fontPath)) {
				// Using built-in fonts
				imagestring($img, 5, 50, 150, "EVENT: " . $name, $textColor);
				imagestring($img, 5, 50, 200, "DATE: " . date('F j, Y', strtotime($date)), $textColor);
				imagestring($img, 5, 50, 250, "TIME: " . $startTime . " - " . $endTime, $textColor);
				imagestring($img, 5, 50, 300, "LOCATION: " . $location, $textColor);
			} else {
				// Using TrueType fonts
				imagettftext($img, 48, 0, 50, 200, $textColor, $fontPath, $name);
				imagettftext($img, 24, 0, 50, 250, $textColor, $fontPath, date('F j, Y', strtotime($date)));
				imagettftext($img, 24, 0, 50, 300, $textColor, $fontPath, $startTime . " - " . $endTime);
				imagettftext($img, 24, 0, 50, 350, $textColor, $fontPath, $location);
			}

			// Save the image
			$fullImagePath = __DIR__ . '/' . $imagePath;
			imagepng($img, $fullImagePath);
			imagedestroy($img);

			// Update the image URL for the event
			$image = $imagePath;

			// If this is a real event from the JSON, update it
			if ($event) {
				$event['image'] = $imagePath;
				// Save back to JSON (you'd need to implement this part)
			}
		}

		// Normalize the date format
		$date = str_replace('-', '/', $date);
		$date = date('Y-m-d', strtotime($date));
		// Find the event with the matching name and date
		foreach ($events as $e) {
			if ($e['name'] === $name && $e['date'] === $date) {
				$event = $e;
				break;
			}
		}
	}

	echo "\t" . '<meta property="og:title" content="' . htmlspecialchars($event['name']) . '">'."\n";
	echo "\t" . '<meta property="og:description" content="' . htmlspecialchars($event['description']) .'">'."\n";
	echo "\t" . '<meta property="og:image" content="/events/' . htmlspecialchars($event['image']) .'">'."\n";
	echo "\t" . '<meta property="og:url" content="' . htmlspecialchars($_SERVER['REQUEST_URI']) .'">'."\n";
	echo "\t" . '<meta name="twitter:card" content="summary_large_image">'."\n";
	echo "\t" . '<meta name="twitter:title" content="' . htmlspecialchars($event['name']) .'">'."\n";
	echo "\t" . '<meta name="twitter:description" content="' . htmlspecialchars($event['description']) .'">'."\n";
	echo "\t" . '<meta name="twitter:image" content="/events/' . htmlspecialchars($event['image']) .'">'."\n";
	echo "\t" . '<link rel="icon" href="/events/' . htmlspecialchars($event['image']) .'" type="image/jpg">'."\n";
?>
</head>
<body>
	<div class="event-container">
		<div id="eventDetails" class="event-tile">
			<!-- Event details will be dynamically loaded here -->
		</div>
	</div>

	<script type="module" src="./js/navigation.js"></script>
	<script>
		// Function to get query parameters from the URL
		function getQueryParams() {
			// Get path segments from URL
			const pathSegments = window.location.pathname.split('/');
			// For a URL like /events/details/event-name/2025-05-17/8
			// pathSegments would be ['', 'events', 'details', 'event-name', '2025-05-17', '8']
			
			const name = pathSegments.length > 3 ? decodeURIComponent(pathSegments[3]) : '';
			const date = pathSegments.length > 4 ? decodeURIComponent(pathSegments[4]) : '';
			const id = pathSegments.length > 5 ? decodeURIComponent(pathSegments[5]) : '';
			
			console.log("Parsed from URL path:", { name, date, id });
			
			return { name, date, id };
		}

		// Normalize date formats for comparison
		function normalizeDateFormat(dateStr) {
			if (!dateStr) return '';
			
			try {
				// Create a new Date object to handle various date formats
				const date = new Date(dateStr);
				if (isNaN(date.getTime())) {
					console.warn("Invalid date:", dateStr);
					return dateStr; // Return original if invalid
				}
				
				// Return in ISO format
				return date.toISOString().split('T')[0]; // YYYY-MM-DD
			} catch (e) {
				console.error("Date normalization error:", e);
				return dateStr;
			}
		}

		// Fetch and display the event
		async function loadEvent() {
			const { name, date, id } = getQueryParams();
			console.log("Processed event name:", name);
			console.log("Processed event date:", date);

			try {
				// Fetch events from the JSON file
				const response = await fetch('events.json');
				const events = await response.json();
				
				// Normalize the target date
				const normalizedTargetDate = normalizeDateFormat(date);
				console.log("Normalized target date:", normalizedTargetDate);

				// Find the event with the matching name and date
				const event = events.find(e => {
					const normalizedEventDate = normalizeDateFormat(e.date);
					console.log(`Comparing: "${e.id}" == "${id}" || ("${e.name}" == "${name}" && "${normalizedEventDate}" == "${normalizedTargetDate}")`);
					return e.id == id || (e.name === name && normalizedEventDate === normalizedTargetDate);
				});

				if (!event) {
					document.getElementById('eventDetails').innerHTML = `
						<p>Event not found.</p>
						<p>Looking for: ${id} or ${name} on ${date}</p>
						<a href="/events/index.html">Go back to the events list</a>
					`;
					return;
				}

				// Convert Zulu time to local time
				if (event.startTime && event.startTime.endsWith("Z")) {
					event.startTime = new Date(event.startTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
				}
				if (event.endTime && event.endTime.endsWith("Z")) {
					event.endTime = new Date(event.endTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
				}

				event.startTime = event.startTime || "00:00";
				event.endTime = event.endTime || "23:59";
				event.date = event.date || 'Error: No date selected.';
				event.guestList = event.guestList || [];
				event.host = event.host || "N/A";
				event.description = event.description || "No description available.";
				event.image = event.image || this.generatePlaceholderImage();
				const [year, month, day] = event.date.split('-');
				event.parsedDate = new Date(Number(year), Number(month) - 1, Number(day)); // Month is 0-based
				// Generate Google Calendar link if we have startTime and endTime
				let googleCalendarLink = '';
				if (event.date && event.startTime && event.endTime) {
					googleCalendarLink = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(event.name)}&dates=${this.formatDateForCalendar(event?.date, event.startTime, event.endTime)}&details=${encodeURIComponent(event.description)}%0D%0A%0D%0A${window.location.href.split('/events/')[0] + event.image}&location=${encodeURIComponent(event.location)}&ctz=EST`;
				}

				// Use the startTime and endTime as they are
				let startTimeFormatted, endTimeFormatted;
				if (this.isISODateFormat(event.startTime)) {
					startTimeFormatted = new Date(event.startTime).toLocaleTimeString();
				} else {
					startTimeFormatted = event.startTime;
				}
				if (this.isISODateFormat(event.endTime)) {
					endTimeFormatted = new Date(event.endTime).toLocaleTimeString();
				} else {
					endTimeFormatted = event.endTime;
				}

				if (!event.googleMapsLink) {
					// Generate Google Maps link from location
					event.googleMapsLink = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(event.location)}`;
				}

				// Unicode emojis for icons
				const locationEmoji = "\u{1F3E0}";
				const pinEmoji = "\u{1F4CC}";
				const calendarEmoji = "\u{1F4C5}";

				// Render the event details
				document.getElementById('eventDetails').innerHTML = `
					<div class="event-image">
						<img src="${event.image || 'placeholder.jpg'}" alt="${event.name}">
					</div>
					<div class="event-details">
						<h2>${event.name}</h2>
						<p class="event-date"><strong>Date:</strong> ${new Date(event.parsedDate).toDateString()}</p>
						<p class="event-time"><strong>Time:</strong> ${event.startTime} - ${event.endTime}</p>
						<p class="event-location"><strong>Location:</strong> ${pinEmoji} <a href="${event.googleMapsLink}" target="_blank" class="google-maps-link">${event.location}</a></p>
						<p class="event-description">${event.description.replace('\n', '<br>\n')}</p>
						<p class="event-host"><strong>Host:</strong> ${event.host || 'N/A'}</p>
						<p class="event-calendar-link">${calendarEmoji} <a href="${googleCalendarLink}" target="_blank">Add to Google Calendar</a></p>
					</div>
					<div class="event-weather">
						<!-- Begin WeatherLink Fragment -->
						<iframe title="Environment Canada Weather" width="296" height="191" src="https://weather.gc.ca/wxlink/wxlink.html?coords=43.655%2C-79.383&lang=e" allowtransparency="true" style="border: 0;"></iframe>
						<!-- End WeatherLink Fragment -->
					</div>
				`;

				// Update the page title with the event name
				document.title = `${event.name} - Event Details`;

				// Update the favicon with the event image
				const favicon = document.createElement('link');
				favicon.rel = 'icon';
				favicon.href = event.image || generatePlaceholderImage();
				document.head.appendChild(favicon);

				// Update the page description for SEO
				const metaDescription = document.createElement('meta');
				metaDescription.name = 'description';
				metaDescription.content = event.description || 'No description available.';
				document.head.appendChild(metaDescription);

				// Update the page keywords for SEO
				const metaKeywords = document.createElement('meta');
				metaKeywords.name = 'keywords';
				metaKeywords.content = `${event.name}, ${event.location}, ${event.date}, event details`;
				document.head.appendChild(metaKeywords);

				// Update the page share tags for social media
				const metaOpenGraph = document.createElement('meta');
				metaOpenGraph.property = 'og:image';
				metaOpenGraph.content = event.image || generatePlaceholderImage();
				document.head.appendChild(metaOpenGraph);
				const metaTwitter = document.createElement('meta');
				metaTwitter.name = 'twitter:image';
				metaTwitter.content = event.image || generatePlaceholderImage();
				document.head.appendChild(metaTwitter);
				const metaTwitterCard = document.createElement('meta');
				metaTwitterCard.name = 'twitter:card';
				metaTwitterCard.content = 'summary_large_image';
				document.head.appendChild(metaTwitterCard);
				const metaTwitterTitle = document.createElement('meta');
				metaTwitterTitle.name = 'twitter:title';
				metaTwitterTitle.content = event.name;
				document.head.appendChild(metaTwitterTitle);
				const metaTwitterDescription = document.createElement('meta');
				metaTwitterDescription.name = 'twitter:description';
				metaTwitterDescription.content = event.description || 'No description available.';
				document.head.appendChild(metaTwitterDescription);
				
			} catch (error) {
				console.error('Error loading event:', error);
				document.getElementById('eventDetails').innerHTML = `
					<p>Failed to load event details.</p>
					<p>Error: ${error.message}</p>
					<a href="/events/index.html">Go back to the events list</a>
				`;
			}
		}

		// Load the event on page load
		loadEvent();
		
		// Generate placeholder image from a calendar emoji
		function generatePlaceholderImage() {
			const canvas = document.createElement("canvas");
			canvas.width = 200;
			canvas.height = 200;
			const ctx = canvas.getContext("2d");

			// Draw a simple calendar icon
			ctx.fillStyle = "#f0f0f0";
			ctx.fillRect(0, 0, canvas.width, canvas.height);
			ctx.fillStyle = "#000";
			ctx.font = "48px Arial";
			ctx.textAlign = "center";
			ctx.textBaseline = "middle";
			ctx.fillText("📅", canvas.width / 2, canvas.height / 2);

			return canvas.toDataURL();
		}

		// Helper method to format date and time for Google Calendar
		function formatDateForCalendar(date, startTime, endTime) {
			// Google Calendar requires the date-time in the format 20211001T100000Z/20211001T110000Z
			// Actual output                                        20250504T140000Z/20250504T170000Z
			const formattedDate = new Date(date).toISOString().split('T')[0].replace(/-/g, '').slice(0, 8); // Format date as YYYYMMDD
			// console.log(formattedDate);

			const startTimeFormatted = formattedDate + 'T' + startTime.replace(/:/g, '') + '00'; // Assuming UTC+5:00
			const endTimeFormatted = formattedDate + 'T' + endTime.replace(/:/g, '') + '00'; // Assuming UTC+5:00

			const dateRangeString = `${startTimeFormatted}/${endTimeFormatted}`;
			// console.log(dateRangeString);
			return dateRangeString;
		}

		// Helper function to check if a date string is in ISO 8601 format
		function isISODateFormat(dateString) {
			// Regular expression to match ISO 8601 date format
			const isoRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(\.\d+)?(Z|[+-]\d{2}:\d{2})?$/;
			return isoRegex.test(dateString);
		}
	</script>
</body>
</html>