<script>
	// Здесь можно, например, проверить, залогинен ли пользователь,
	// или добавить любую другую логику, специфичную для админки.
</script>

<div class="admin-container">
	<!-- Шапка админки -->
	<header class="admin-header">
		<h1>Admin Panel</h1>
		<nav>
			<a href="/admin">Orders</a>
			<!-- Можно добавить другие ссылки -->
			<!-- <a href="/admin/products">Products</a> -->
		</nav>
	</header>

	<!-- Основной блок, где будут рендериться страницы админки -->
	<main class="admin-main">
		<slot />
	</main>
</div>

<style>
	.admin-container {
		min-height: 100vh;
		background-color: #f0f0f0; /* Например, светло-серый фон */
	}

	.admin-header {
		background-color: #333;
		color: #fff;
		padding: 1rem;
	}

	.admin-header h1 {
		margin: 0;
		display: inline-block;
		margin-right: 2rem;
	}

	.admin-header nav a {
		color: #fff;
		margin-right: 1rem;
		text-decoration: none;
	}

	.admin-main {
		padding: 1rem;
	}
</style>
