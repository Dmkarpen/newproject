<script>
	import AppLoader from '$lib/components/AppLoader.svelte';
</script>

<AppLoader />

<div class="min-h-screen bg-base-200">
	<!-- Шапка адмінки -->
	<header class="bg-neutral text-neutral-content p-4 flex items-center justify-between">
		<!-- Зробили клікабельним логотип -->
		<a href="/admin" class="text-xl font-bold hover:underline">Admin Panel</a>
		<nav class="space-x-4">
			<a href="/admin" class="link link-hover">Orders</a>
			<!-- <a href="/admin/products" class="link link-hover">Products</a> -->
		</nav>
	</header>

	<!-- Контент -->
	<main class="p-6">
		<slot />
	</main>
</div>

<style>
	/* без стилей */
</style>
