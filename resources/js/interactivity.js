document.querySelectorAll('.animate-on-scroll').forEach((el) => {
	const observer = new IntersectionObserver(
		([entry]) => {
			if (entry.isIntersecting) {
				el.classList.add('is-visible');
			}
		},
		{ threshold: 0.05, rootMargin: '-20px' }
	);
	observer.observe(el);
});
