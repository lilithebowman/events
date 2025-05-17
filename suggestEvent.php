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
		try {
			// Process the form - in a real application, you might:
			// - Send an email to the administrator
			// - Store in a database for review
			// - Send a confirmation to the user
			
			// For this example, we'll just set a success flag
			$formSuccess = true;
			
			// Optional: Log the suggestion
			$logFile = 'logs/event_suggestions.json';
			if (!is_dir('logs')) {
				mkdir('logs', 0755, true);
			}

			// load current log file
			if (file_exists($logFile)) {
				$jsonData = file_get_contents($logFile);
				$logData = json_decode($jsonData, true);
				if ($logData === null) {
					$logData = [];
				}
			} else {
				$logData = [];
			}

			// Append information about the client's IP and browser details to the log entry
			$clientIP = $_SERVER['REMOTE_ADDR'];
			$userAgent = $_SERVER['HTTP_USER_AGENT'];

			// Create log entry with sanitized values
			$logEntry = [
				'name' => strip_tags($_POST['name'] ?? ''),
				'date' => strip_tags($_POST['date'] ?? ''),
				'startTime' => strip_tags($_POST['startTime'] ?? ''),
				'endTime' => strip_tags($_POST['endTime'] ?? ''),
				'location' => strip_tags($_POST['location'] ?? ''),
				'description' => $_POST['description'] ?? '',
				'host' => strip_tags($_POST['host'] ?? ''),
				'email' => strip_tags($_POST['email'] ?? ''),
				'clientIP' => $clientIP,
				'userAgent' => $userAgent,
				'timestamp' => date('Y-m-d H:i:s')
			];

			// append the new log entry to the existing log data
			$logData[] = $logEntry;
			$output = json_encode($logData, JSON_PRETTY_PRINT);

			// Write the updated log data back to the file
			file_put_contents($logFile, $output);
		} catch (Exception $e) {
			$formError = 'Failed to log the event suggestion: ' . $e->getMessage();
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<base href="/events/">
	
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Suggest an Event - Lilithe's Toronto Furry Events List</title>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
	<link rel="stylesheet" href="./css/EventsList.css">

	<!-- Share tags -->
	<meta property="og:title" content="Suggest an Event - Lilithe's Toronto Furry Events List">
	<meta property="og:type" content="website">
	<meta property="og:description" content="Suggest a new event for the furry community in Toronto.">
	<meta property="og:site_name" content="Lilithe's Toronto Furry Events List">
	<meta property="og:locale" content="en_CA">
	<meta property="og:image" content="uploads/event_680ea6ae3c4ca9.79115047.JPG">
	<meta property="og:url" content="https://www.lilithebowman.com/events/suggest">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="Suggest an Event - Lilithe's Toronto Furry Events List">
	<meta name="twitter:description" content="Suggest a new event for the furry community in Toronto.">
	<meta name="twitter:site" content="@notHereAnymoBye">
	<meta name="twitter:image" content="uploads/event_680ea6ae3c4ca9.79115047.JPG">
	<link rel="icon" href="uploads/event_680ea6ae3c4ca9.79115047.JPG" type="image/jpg">
	
	<script type="module" src="./js/modules.js"></script>
</head>
<body>
	<script type="module" src="./js/navigation.js"></script>
	<div class="form-container">
		<h1>Suggest an Event</h1>
		
		<?php if ($formSuccess): ?>
			<div class="alert alert-success">
				<p>Thank you for your event suggestion! It has been submitted for review.</p>
				<p><a href="index.html" class="btn-primary">Return to Events Page</a></p>
			</div>
		<?php elseif ($formError): ?>
			<div class="alert alert-danger">
				<p><?php echo htmlspecialchars($formError); ?></p>
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
				<div class="toolbar">
					<button type="button" data-command="bold" class="rt-toggle-bold">Bold</button>
					<button type="button" data-command="italic" class="rt-toggle-italic">Italic</button>
					<button type="button" data-command="strikethrough" class="rt-toggle-st">Strikethrough</button>
					<button type="button" data-command="createLink" class="rt-link">Link</button>
					<button type="button" data-command="insertHorizontalRule" class="rt-divider">Divider</button>
				</div>
				<div class="form-group">
					<label for="description">Description</label>
					<div id="description" name="description" class="editor" contenteditable="true"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></div>
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

	<script type="module">
		import { initEditor } from './js/richText.js';

		document.addEventListener('DOMContentLoaded', function() {
			const editor = document.querySelector('#description');
			const toolbar = document.querySelector('.toolbar');

			initEditor(editor, toolbar);

			toolbar.addEventListener('click', function(event) {
				if (event.target.tagName === 'BUTTON') {
					const command = event.target.dataset.command;

					if (command === 'createLink') {
						const url = prompt('Enter the link URL:');
						if (url) {
							document.execCommand(command, false, url);
						}
					} else {
						document.execCommand(command, false, null);
					}
				}
			});
		});
	</script>
</body>
</html>