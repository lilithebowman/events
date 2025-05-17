/**
 * Rich Text Editor
 * @module richText
 * @description This module provides a rich text editor for creating and editing content.
 */

const initEditor = (editor, toolbar) => {
	// Initialize the toolbar buttons
	const buttons = toolbar.querySelectorAll('button');
	buttons.forEach(button => {
		button.addEventListener('click', () => {
			const command = button.dataset.command;
			if (command === 'createLink') {
				const url = prompt('Enter the link URL:', 'http://');
				if (url) {
					formatText('createLink', url);
					return;
				}
			} else {
				formatText(command);
			}
		});
	});
	// Initialize the editor
	editor.contentEditable = true;
	editor.focus();

	// Keyboard input handler
	editor.addEventListener('keydown', handleKeyDown);
}

/**
 * Handle keyboard input for specific shortcuts.
 * @function handleKeyDown
 * @param {KeyboardEvent} event - The keyboard event.
 */
const handleKeyDown = (event) => {
	if (event.ctrlKey) {
		switch (event.key.toLowerCase()) {
			case 'b':
				event.preventDefault(); // Prevent default browser behavior
				formatText('bold');
				break;
			case 'i':
				event.preventDefault(); // Prevent default browser behavior
				formatText('italic');
				break;
		}
	} else if (event.key === 'Enter') {
		event.preventDefault();
		insertParagraph();
	}
}

/**
 * Format the selected text in the editor.
 * @function formatText
 * @param {string} command - The formatting command (e.g., 'bold', 'italic', 'createLink').
 * @param {string} [value] - The value for the command (e.g., URL for 'createLink').
 */
const formatText = (command, value = null) => {
	const selection = window.getSelection();
	if (selection.rangeCount > 0) {
		const range = selection.getRangeAt(0);
		const selectedText = range.toString();

		if (selectedText) {
			let element;
			if (command === 'bold') {
				element = document.createElement('strong');
			} else if (command === 'italic') {
				element = document.createElement('em');
			} else if (command === 'strikethrough') {
				element = document.createElement('s');
			} else if (command === 'underline') {
				element = document.createElement('u');
			} else if (command === 'insertHorizontalRule') {
				const hr = document.createElement('hr');
				range.insertNode(hr);
				return;
			} else if (command === 'createLink' && value) {
				element = document.createElement('a');
				element.href = value;
				element.target = '_blank'; // Open links in new tab
			} else {
				element = document.createElement('span');
			}

			element.textContent = selectedText;
			range.deleteContents();
			range.insertNode(element);
		}
	}
}

/**
 * Insert a new paragraph.
 * @function insertParagraph
 */
const insertParagraph = () => {
	const selection = window.getSelection();
	if (selection.rangeCount > 0) {
		const range = selection.getRangeAt(0);
		// end the previous paragraph
		const endNode = range.endContainer;
		if (endNode.nodeType === Node.TEXT_NODE) {
			const parent = endNode.parentNode;
			const text = endNode.textContent;
			endNode.textContent = text.substring(0, range.endOffset);
			range.setStartAfter(endNode);
		} else if (endNode.nodeType === Node.ELEMENT_NODE) {
			const text = endNode.textContent;
			endNode.textContent = text.substring(0, range.endOffset);
			range.setStartAfter(endNode);
		}

		// insert a new paragraph
		const p = document.createElement('p');
		range.insertNode(p);
		range.setStart(p, 0);
		range.setEnd(p, 0);
	}
}

// Export the initEditor function for external use
export { initEditor };
