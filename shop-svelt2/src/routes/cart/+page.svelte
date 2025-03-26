<script>
	import { onMount } from 'svelte';
	import { createProductsCart } from '../../runes/cartProducts.svelte';
	import { isLoading } from '../../lib/stores/loading';

	const {
		cartProducts,
		totalPrice,
		clearCart,
		deleteProductFromCart,
		plusProductFromCart,
		minusProductFromCart
	} = createProductsCart();

	let showOrderForm = false;
	let name = '';
	let phone = '';
	let address = '';

	let stockErrors = []; // Список ID товарів, в яких є проблема з кількістю
	let stockMessages = []; // Тексти помилок для відображення

	function onQuantityChange(index) {
		// чекаємо до наступного циклу подій, щоб count вже був оновлений
		setTimeout(() => {
			checkStock();
		}, 0);
	}

	function toggleOrderForm() {
		showOrderForm = true;
	}

	async function checkStock() {
		isLoading.set(true);
		const ids = cartProducts.map((p) => p.id);
		try {
			const res = await fetch('http://127.0.0.1:8000/api/products/stock-check', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ ids })
			});

			if (!res.ok) throw new Error('Failed to check stock');

			const products = await res.json();
			stockErrors = [];
			stockMessages = [];

			for (const cartItem of cartProducts) {
				const matched = products.find((p) => p.id === cartItem.id);
				if (matched && cartItem.count > matched.stock) {
					stockErrors.push(cartItem.id);
					stockMessages.push(`${cartItem.title}: only ${matched.stock} left in stock`);
				}
			}
		} catch (e) {
			console.error('Stock check error:', e);
		} finally {
			isLoading.set(false);
		}
	}

	async function submitOrder() {
		isLoading.set(true);
		await checkStock();
		if (stockErrors.length > 0) {
			isLoading.set(false);
			return;
		}

		const items = cartProducts.map((p) => ({
			id: p.id,
			title: p.title,
			price: p.price,
			count: p.count
		}));
		const total = totalPrice(cartProducts);

		const orderData = {
			name,
			phone,
			address,
			items,
			total
		};

		try {
			const res = await fetch('http://127.0.0.1:8000/api/orders', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(orderData)
			});

			if (!res.ok) throw new Error(`Server error: ${res.status}`);

			await res.json();
			clearCart();
			showOrderForm = false;
			name = '';
			phone = '';
			address = '';
		} catch (error) {
			console.error('Order error:', error);
		} finally {
			isLoading.set(false);
		}
	}

	onMount(() => {
		checkStock();
	});
</script>

{#if stockMessages.length > 0}
	<div class="alert alert-error my-4">
		<h3 class="font-bold">Stock issues found:</h3>
		<ul>
			{#each stockMessages as msg}
				<li>{msg}</li>
			{/each}
		</ul>
	</div>
{/if}

<div style="text-align:center;">
	<h1>Welcome to Cart Products</h1>
	<div class="overflow-x-auto">
		<table class="table">
			<!-- head -->
			<thead>
				<tr>
					<th>
						<label>
							<input type="checkbox" class="checkbox" />
						</label>
					</th>
					<th>Name</th>
					<th>Description</th>
					<th>Price</th>
					<th>Count</th>
					<!-- Добавили столбец для количества -->
					<th></th>
					<!-- Столбец для кнопки удаления -->
				</tr>
			</thead>
			<tbody>
				{#each cartProducts as product, index}
					<tr class={stockErrors.includes(product.id) ? 'bg-red-100' : ''}>
						<!-- Чекбокс -->
						<th>
							<label>
								<input type="checkbox" class="checkbox" />
							</label>
						</th>

						<!-- Название / Категория -->
						<td>
							<div class="flex items-center gap-3">
								<div class="avatar">
									<div class="mask mask-squircle h-12 w-12">
										<img src={product.thumbnail} alt={product.title} />
									</div>
								</div>
								<div>
									<div class="font-bold">{product.title}</div>
									<div class="text-sm opacity-50">{product.category}</div>
								</div>
							</div>
						</td>

						<!-- Описание -->
						<td>
							{product.description}
							<br />
							<span class="badge badge-ghost badge-sm">{product.warrantyInformation}</span>
						</td>

						<!-- Цена (одной единицы) -->
						<td><strong>{product.price} $</strong></td>

						<!-- Столбец Count: кнопки и отображение -->
						<td>
							<button
								class="btn btn-xs"
								on:click={() => {
									minusProductFromCart(index);
									onQuantityChange(index);
								}}
							>
								-
							</button>

							<span class="mx-2">{product.count}</span>

							<button
								class="btn btn-xs"
								on:click={() => {
									plusProductFromCart(index);
									onQuantityChange(index);
								}}
							>
								+
							</button>
						</td>

						<!-- Кнопка удаления всей позиции -->
						<td>
							<button class="btn" on:click={() => deleteProductFromCart(index)}>
								<svg
									class="w-6 h-6"
									viewBox="0 0 24 24"
									fill="none"
									xmlns="http://www.w3.org/2000/svg"
								>
									<path
										d="M4 7H20"
										stroke="#ff0000"
										stroke-width="2"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
									<path
										d="M6 7V18C6 19.6569 7.34315 21 9 21H15C16.6569 21 18 19.6569 18 18V7"
										stroke="#ff0000"
										stroke-width="2"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
									<path
										d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
										stroke="#ff0000"
										stroke-width="2"
										stroke-linecap="round"
										stroke-linejoin="round"
									/>
								</svg>
							</button>
						</td>
					</tr>
				{/each}
			</tbody>
			<!-- foot -->
			<tfoot>
				<tr>
					<th></th>
					<th></th>
					<th class="text-right">Total</th>
					<th>{totalPrice(cartProducts)} $</th>
					<th></th>
					<th></th>
				</tr>
			</tfoot>
		</table>
	</div>

	<!-- Кнопка "Order" (показываем только если showOrderForm == false и cartProducts.length > 0) -->
	{#if !showOrderForm && cartProducts.length > 0}
		<button
			class="btn btn-primary q-mt-md"
			disabled={stockErrors.length > 0}
			on:click={toggleOrderForm}
		>
			Order
		</button>
	{/if}

	<!-- Если showOrderForm, показываем форму -->
	{#if showOrderForm}
		<!-- Блок формы -->
		<div class="q-mt-md" style="max-width: 300px; margin: 0 auto; text-align: left;">
			<form on:submit|preventDefault={submitOrder}>
				<div class="q-mb-md">
					<label>Name:</label>
					<input
						type="text"
						bind:value={name}
						placeholder="Your name"
						class="input input-bordered w-full"
						required
					/>
				</div>
				<div class="q-mb-md">
					<label>Phone number:</label>
					<input
						type="text"
						bind:value={phone}
						placeholder="Your phone"
						class="input input-bordered w-full"
						required
					/>
				</div>
				<div class="q-mb-md">
					<label>Address:</label>
					<input
						type="text"
						bind:value={address}
						placeholder="Your address"
						class="input input-bordered w-full"
						required
					/>
				</div>
				<!-- Кнопка Submit -->
				<div style="margin-top: 1rem; text-align: center;">
					<button type="submit" class="btn btn-success"> Submit </button>
				</div>
			</form>
		</div>
	{/if}
</div>
