<?php
// filepath: /home/lilithe/public_html/events/suggestEvent.php
// Check if form was submitted
$formSubmitted = isset($_POST['submit']);
$formSuccess = false;
$formError = '';

if ($formSubmitted) {
    // Basic validation
    if (empty($_POST['name']) || empty($_POST['date']) || empty($_POST['location'])) {
        $formError = 'Please fill in all required fields.';
    } else {
        // Process the form - in a real application, you might:
        // - Send an email to the administrator
        // - Store in a database for review
        // - Send a confirmation to the user
        
        // For this example, we'll just set a success flag
        $formSuccess = true;
        
        // Optional: Log the suggestion
        $logFile = 'logs/event_suggestions.log';
        if (!is_dir('logs')) {
            mkdir('logs', 0755, true);
        }
        
        $logEntry = date('Y-m-d H:i:s') . " | " . 
                   "Name: " . $_POST['name'] . " | " .
                   "Date: " . $_POST['date'] . " | " .
                   "Location: " . $_POST['location'] . " | " .
                   "Suggested by: " . ($_POST['email'] ?? 'Anonymous') . "\n";

		// Append information about the client's IP and browser details to the log entry
		$clientIP = $_SERVER['REMOTE_ADDR'];
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$logEntry .= "Client IP: $clientIP | User Agent: $userAgent\n";
		$logEntry .= "----------------------------------------\n";

		// Ensure the log is escaped so it does not break the file format
		$logEntry = htmlspecialchars($logEntry, ENT_QUOTES, 'UTF-8');
		
		// Write to log file           
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
	<base href="/events/">
    
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Agenda of Local Events</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
	<link rel="stylesheet" href="./css/EventsList.css">

	<!-- Share tags -->
	<meta property="og:title" content="Lilithe's Toronto Furry Events List">
	<meta property="og:type" content="website">
	<meta property="og:description" content="A comprehensive list of furry events in Toronto.">
	<meta property="og:site_name" content="Lilithe's Toronto Furry Events List">
	<meta property="og:locale" content="en_CA">
	<meta property="og:image" content="uploads/event_680ea6ae3c4ca9.79115047.JPG">
	<meta property="og:url" content="https://www.lilithebowman.com/events/">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="Lilithe's Toronto Furry Events List">
	<meta name="twitter:description" content="A comprehensive list of furry events in Toronto.">
	<meta name="twitter:site" content="@notHereAnymoBye">
	<meta name="twitter:image" content="uploads/event_680ea6ae3c4ca9.79115047.JPG">
	<link rel="icon" href="uploads/event_680ea6ae3c4ca9.79115047.JPG" type="image/jpg">
	

	<script type="module" src="./js/modules.js"></script>
</head>
<body>
	<script>
		
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
			link.addEventListener('click', function(event) {
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
		suggestEventButton.querySelector('a').addEventListener('click', function(event) {
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
	</script>
    <div class="form-container">
        <h1>Suggest an Event</h1>
        
        <?php if ($formSuccess): ?>
            <div class="alert alert-success">
                <p>Thank you for your event suggestion! It has been submitted for review.</p>
                <p><a href="index.html" class="btn-primary">Return to Events Page</a></p>
            </div>
        <?php elseif ($formError): ?>
            <div class="alert alert-danger">
                <p><?php echo $formError; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (!$formSuccess): ?>
            <form method="post" action="suggest" class="event-form">
                <div class="form-group">
                    <label for="name">Event Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="date">Date <span class="required">*</span></label>
                    <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>" class="form-control">
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-half">
                        <label for="startTime">Start Time</label>
                        <input type="time" id="startTime" name="startTime" value="<?php echo htmlspecialchars($_POST['startTime'] ?? ''); ?>" class="form-control">
                    </div>
                    
                    <div class="form-group form-group-half">
                        <label for="endTime">End Time</label>
                        <input type="time" id="endTime" name="endTime" value="<?php echo htmlspecialchars($_POST['endTime'] ?? ''); ?>" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location <span class="required">*</span></label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="host">Host/Organizer</label>
                    <input type="text" id="host" name="host" value="<?php echo htmlspecialchars($_POST['host'] ?? ''); ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="email">Your Email (for follow-up questions)</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" class="form-control">
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="submit" class="btn-submit">Submit Event Suggestion</button>
                    <a href="index.html" class="btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>