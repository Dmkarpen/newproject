<script>
	import { isLoading } from '../stores/loading';
	import { onDestroy } from 'svelte';
	import { tick } from 'svelte';

	let show = false;
	let timeout;

	const unsubscribe = isLoading.subscribe(async (val) => {
		if (val) {
			// невелика затримка перед показом, щоб уникнути "мигання"
			timeout = setTimeout(() => (show = true), 10);
		} else {
			clearTimeout(timeout);
			await tick();
			show = false;
		}
	});

	onDestroy(() => {
		unsubscribe();
		clearTimeout(timeout);
	});
</script>

{#if show}
	<div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
		<div
			class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12"
		></div>
	</div>
{/if}

<style>
	.loader {
		border-top-color: #3498db;
		animation: spin 1s linear infinite;
	}

	@keyframes spin {
		0% {
			transform: rotate(0deg);
		}
		100% {
			transform: rotate(360deg);
		}
	}
</style>
