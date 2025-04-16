<script>
	import { onMount } from 'svelte';
	import { createProductsCart } from '../../runes/cartProducts.svelte';
	import { isLoading } from '../../lib/stores/loading';
	import SearchSelect from '$lib/components/SearchSelect.svelte';

	const {
		cartProducts,
		totalPrice,
		clearCart,
		deleteProductFromCart,
		plusProductFromCart,
		minusProductFromCart,
		addProductToCart
	} = createProductsCart();

	let showOrderForm = false;
	let name = '';
	let phone = '';
	let address = '';
	let deliveryType = 'pickup';
	let stockErrors = [];
	let stockMessages = [];
	let orderSuccessMessage = '';

	let cities = [];
	let warehouses = [];
	let selectedCity = null;
	let selectedWarehouse = null;

	async function fetchCities() {
		try {
			const res = await fetch('http://127.0.0.1:8000/api/novaposhta/cities');
			const data = await res.json();
			cities = data.data || [];
		} catch (err) {
			console.error('Fetch cities error:', err);
		}
	}

	async function fetchWarehouses(city) {
		if (!city?.Ref) return;

		try {
			const res = await fetch('http://127.0.0.1:8000/api/novaposhta/warehouses', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ cityRef: city.Ref })
			});
			const data = await res.json();
			warehouses = data.data || [];
		} catch (err) {
			console.error('Fetch warehouses error:', err);
		}
	}

	$: if (selectedCity) {
		fetchWarehouses(selectedCity);
	}

	function onQuantityChange(index) {
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
			delivery_type: deliveryType,
			np_city_ref: selectedCity ? selectedCity.Ref : '',
			items,
			total
		};

		if (deliveryType === 'courier') {
			orderData.address = address;
		} else if (deliveryType === 'pickup') {
			orderData.np_warehouse_ref = selectedWarehouse ? selectedWarehouse.Ref : '';
		}

		try {
			const res = await fetch('http://127.0.0.1:8000/api/orders', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(orderData)
			});

			if (!res.ok) throw new Error(`Server error: ${res.status}`);

			const responseData = await res.json();
			clearCart();
			showOrderForm = false;
			name = '';
			phone = '';
			address = '';
			selectedCity = null;
			selectedWarehouse = null;
			orderSuccessMessage = `Your order has been successfully placed! Order #${responseData.order_id}`;
		} catch (error) {
			console.error('Order error:', error);
		} finally {
			isLoading.set(false);
		}
	}

	onMount(() => {
		checkStock();
		fetchCities();
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

{#if orderSuccessMessage}
	<div class="alert alert-success my-4">
		<span>{orderSuccessMessage}</span>
	</div>
{/if}

<div style="text-align:center;">
	<h1 class="text-4xl font-bold text-center my-8 text-primary">🛒 Your Shopping Cart</h1>
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
		<div class="q-mt-md" style="max-width: 400px; margin: 0 auto; text-align: left;">
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

				<div class="my-6">
					<label>Delivery method:</label>
					<div class="flex gap-4 mt-2">
						<label class="flex items-center gap-2">
							<input type="radio" bind:group={deliveryType} value="pickup" />
							Pickup from Nova Poshta
						</label>
						<label class="flex items-center gap-2">
							<input type="radio" bind:group={deliveryType} value="courier" />
							Courier to your address
						</label>
					</div>
				</div>

				{#if deliveryType === 'pickup' || deliveryType === 'courier'}
					<div class="q-mb-md">
						<label>City:</label>
						<SearchSelect
							items={cities}
							bind:selected={selectedCity}
							placeholder="Choose city"
							label="Description"
							minSearchLength={3}
							useStartsWith={true}
						/>
					</div>
				{/if}

				{#if deliveryType === 'pickup' && warehouses.length > 0}
					<div class="q-mb-md">
						<label>Warehouse:</label>
						<SearchSelect
							items={warehouses}
							bind:selected={selectedWarehouse}
							placeholder="Choose warehouse"
							label="Description"
							minSearchLength={0}
							useStartsWith={false}
						/>
					</div>
				{/if}

				{#if deliveryType === 'courier'}
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
				{/if}

				<div style="margin-top: 1rem; text-align: center;">
					<button type="submit" class="btn btn-success">Submit</button>
				</div>
			</form>
		</div>
	{/if}
</div>
