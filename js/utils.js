export const formatDateForCalendar = (date, startTime, endTime) => {
	// Validate inputs
	if (!date || typeof date !== 'string' || !date.includes('-')) {
		console.error('Invalid date format. Expected format: YYYY-MM-DD');
		return '';
	}

	if (!startTime || typeof startTime !== 'string' || !startTime.includes(':')) {
		console.error('Invalid start time format. Expected format: HH:MM a.m./p.m.');
		return '';
	}

	if (!endTime || typeof endTime !== 'string' || !endTime.includes(':')) {
		console.error('Invalid end time format. Expected format: HH:MM a.m./p.m.');
		return '';
	}

	// Google Calendar requires the date-time in the format 20211001T100000Z/20211001T110000Z
	// console.log('date', date);
	// console.log('startTime', startTime);
	// console.log('endTime', endTime);

	let dateFormatted = date.replace('-', '').replace('-', ''); // Remove dashes from the date string
	let startTimeFormatted = startTime;
	let endTimeFormatted = endTime;

	// console.log('dateFormatted', dateFormatted);

	// If PM, add 12 hours to the start time, upper or lower case
	if (startTime.toLowerCase().indexOf("p.m.") != -1 && startTime.split(':')[0] !== '12') {
		const [hours, minutes] = startTime.split(':');
		startTimeFormatted = `${parseInt(hours) + 12}:${minutes}`;
	}
	// If AM, set hours to 0 if it's 12
	if (startTime.toLowerCase().indexOf("a.m.") != -1 && startTime.split(':')[0] === '12') {
		const [hours, minutes] = startTime.split(':');
		startTimeFormatted = `00:${minutes}`;
	}
	// If PM, add 12 hours to the end time
	if (endTime.toLowerCase().indexOf("p.m.") != -1 && endTime.split(':')[0] !== '12') {
		const [hours, minutes] = endTime.split(':');
		endTimeFormatted = `${parseInt(hours) + 12}:${minutes}`;
	}
	// If AM, set hours to 0 if it's 12
	if (endTime.toLowerCase().indexOf("a.m.") != -1 && endTime.split(':')[0] === '12') {
		const [hours, minutes] = endTime.split(':');
		endTimeFormatted = `00:${minutes}`;
	}
	// Trim whitespace
	startTimeFormatted = startTimeFormatted.trim();
	endTimeFormatted = endTimeFormatted.trim();
	// Remove a.m. and p.m. from the time strings
	startTimeFormatted = startTimeFormatted.toLowerCase().replace(/a\.m\./i, '').replace(/p\.m\./i, '');
	endTimeFormatted = endTimeFormatted.toLowerCase().replace(/a\.m\./i, '').replace(/p\.m\./i, '');
	// console.log('startTime', startTime);
	// Remove colons from the time strings
	startTimeFormatted = startTimeFormatted.replace(/:/g, '');
	endTimeFormatted = endTimeFormatted.replace(/:/g, '');

	// Format the start and end times
	const startDateFormatted = dateFormatted + 'T' + startTimeFormatted.replace(' ', '') + '00';
	const endDateFormatted = dateFormatted + 'T' + endTimeFormatted.replace(' ', '') + '00';

	// console.log('startDateFormatted', startDateFormatted);
	// console.log('endDateFormatted', endDateFormatted);

	const dateRangeString = `${startDateFormatted}/${endDateFormatted}`;
	// console.log(dateRangeString);
	return dateRangeString;
}