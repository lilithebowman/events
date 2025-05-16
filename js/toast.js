export class Toast {
	// Function to create and display a toast
	showToast = (message, type = 'error') => {
		const toastContainer = document.querySelector('.toast-container') || this.createToastContainer();
		const toast = document.createElement('div');
		toast.className = `toast ${type}`;
		toast.innerHTML = `
		<span class="toast-message">${message}</span>
		<button class="toast-close">&times;</button>
	`;

		// Add close functionality
		toast.querySelector('.toast-close').addEventListener('click', () => {
			toast.remove();
		});

		// Auto-remove toast after 5 seconds
		setTimeout(() => toast.remove(), 5000);

		toastContainer.appendChild(toast);
	}

	// Function to create the toast container if it doesn't exist
	createToastContainer = () => {
		const container = document.createElement('div');
		container.className = 'toast-container';
		document.body.appendChild(container);
		return container;
	}
}

export default Toast;
