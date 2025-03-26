<script>
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import { isLoading } from '$lib/stores/loading';

	let orderId = $page.params.id;
	let order = null;
	let stockMap = {}; // id -> stock
	let stockErrors = []; // список product_id з помилками

	let successMessage = '';
	let errorMessage = '';

	let showProductModal = false;
	let productSearch = '';
	let searchResults = [];

	onMount(async () => {
		isLoading.set(true);
		try {
			const res = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}`);
			if (!res.ok) {
				errorMessage = 'Error loading order: ' + (await res.text());
				return;
			}
			order = await res.json();
			await checkStock();
		} catch (err) {
			errorMessage = 'Error fetching order: ' + err;
		} finally {
			isLoading.set(false);
		}
	});

	$: if (order && order.items) {
		let sum = 0;
		for (const item of order.items) {
			sum += item.price * item.count;
		}
		order.total = parseFloat(sum.toFixed(2));
	}

	async function checkStock() {
		if (!order?.items?.length) return;
		const ids = [...new Set(order.items.map((i) => i.product_id))];
		try {
			const res = await fetch('http://127.0.0.1:8000/api/products/stock-check', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ ids })
			});

			const products = await res.json();
			stockMap = {};
			products.forEach((p) => {
				stockMap[p.id] = p.stock;
			});

			stockErrors = [];
			order.items.forEach((item) => {
				if (item.count > (stockMap[item.product_id] ?? 0)) {
					stockErrors.push(item.product_id);
				}
			});
		} catch (e) {
			console.error('Failed to check stock:', e);
		}
	}

	async function updateOrder() {
		successMessage = '';
		errorMessage = '';
		isLoading.set(true);

		await checkStock();
		if (stockErrors.length > 0) {
			errorMessage = 'Some products exceed stock availability';
			isLoading.set(false);
			return;
		}

		const body = {
			name: order.name,
			phone: order.phone,
			address: order.address,
			total: order.total,
			status: order.status,
			items: order.items.map((i) => ({
				id: i.id,
				product_id: i.product_id,
				title: i.title,
				price: i.price,
				count: i.count
			}))
		};

		try {
			const res = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}`, {
				method: 'PUT',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(body)
			});
			if (res.ok) {
				const data = await res.json();
				order = data.order;
				successMessage = data.message || 'The order is updated.';
			} else {
				errorMessage = 'Error updating order: ' + (await res.text());
			}
		} catch (err) {
			errorMessage = 'Error updating order: ' + err;
		} finally {
			isLoading.set(false);
		}
	}

	function openProductModal() {
		showProductModal = true;
		productSearch = '';
		searchResults = [];
	}

	function closeProductModal() {
		showProductModal = false;
		productSearch = '';
		searchResults = [];
	}

	async function searchProducts() {
		if (!productSearch.trim()) return;
		const res = await fetch(`http://127.0.0.1:8000/api/products/search?q=${productSearch}`);
		if (res.ok) {
			searchResults = await res.json();
		}
	}

	function selectProduct(product) {
		order.items.push({
			id: null,
			product_id: product.id,
			title: product.title,
			price: product.price,
			count: 1
		});
		order.items = [...order.items];
		closeProductModal();
		checkStock();
	}

	async function removeItem(item, index) {
		successMessage = '';
		errorMessage = '';

		if (!item.id) {
			order.items.splice(index, 1);
			order.items = [...order.items];
			return;
		}

		isLoading.set(true);
		try {
			const res = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}/items/${item.id}`, {
				method: 'DELETE',
				headers: { 'Content-Type': 'application/json' }
			});
			if (res.ok) {
				const data = await res.json();
				order = data.order;
				successMessage = data.message || 'Item removed successfully';
			} else {
				errorMessage = 'Error removing item: ' + (await res.text());
			}
		} catch (err) {
			errorMessage = 'Error removing item: ' + err;
		} finally {
			isLoading.set(false);
		}
	}
</script>

{#if order}
	<h2 class="text-2xl font-bold mb-4">Edit Order #{order.id}</h2>

	{#if successMessage}
		<div class="alert alert-success mb-4">{successMessage}</div>
	{/if}
	{#if errorMessage}
		<div class="alert alert-error mb-4">{errorMessage}</div>
	{/if}

	<!-- Верхний блок редактирования -->
	<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 max-w-5xl">
		<div>
			<label class="label">Name</label>
			<input class="input input-bordered w-full" bind:value={order.name} />
		</div>
		<div>
			<label class="label">Phone</label>
			<input class="input input-bordered w-full" bind:value={order.phone} />
		</div>
		<div class="md:col-span-2">
			<label class="label">Address</label>
			<input class="input input-bordered w-full" bind:value={order.address} />
		</div>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 max-w-5xl">
		<div>
			<label class="label">Total</label>
			<input class="input input-bordered w-full" type="number" bind:value={order.total} disabled />
		</div>
		<div>
			<label class="label">Status</label>
			<select class="select select-bordered w-full" bind:value={order.status}>
				<option value="pending">Pending</option>
				<option value="processing">Processing</option>
				<option value="completed">Completed</option>
				<option value="cancelled">Cancelled</option>
			</select>
		</div>
	</div>

	<h3 class="text-lg font-semibold mb-2">Order Items</h3>
	<div class="overflow-x-auto">
		<table class="table table-zebra w-full">
			<thead>
				<tr>
					<th>ID</th>
					<th>Product ID</th>
					<th>Title</th>
					<th>Price</th>
					<th>Count</th>
					<th>Stock</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{#each order.items as item, index}
					<tr class={stockErrors.includes(item.product_id) ? 'bg-red-100' : ''}>
						<td>{item.id ?? 'new'}</td>
						<td>{item.product_id}</td>
						<td>{item.title}</td>
						<td>{item.price}</td>
						<td
							><input
								type="number"
								class="input input-bordered input-sm w-20"
								bind:value={item.count}
								min="1"
								on:change={checkStock}
							/></td
						>
						<td>{stockMap[item.product_id] ?? '-'}</td>
						<td
							><button class="btn btn-sm btn-error" on:click={() => removeItem(item, index)}
								>Delete</button
							></td
						>
					</tr>
				{/each}
			</tbody>
		</table>
	</div>

	<div class="flex gap-4 mt-4">
		<button class="btn btn-success" on:click={openProductModal}>Add item</button>
		<button class="btn btn-primary" on:click={updateOrder} disabled={stockErrors.length > 0}
			>Save</button
		>
	</div>

	<!-- Modal -->
	{#if showProductModal}
		<dialog class="modal modal-open">
			<div class="modal-box">
				<h3 class="font-bold text-lg mb-2">Add Product to Order</h3>
				<input
					type="text"
					placeholder="Search products..."
					class="input input-bordered w-full mb-2"
					bind:value={productSearch}
					on:input={searchProducts}
				/>

				<ul class="max-h-64 overflow-auto">
					{#each searchResults as product}
						<li>
							<button
								class="btn btn-sm btn-ghost justify-start w-full"
								on:click={() => selectProduct(product)}
							>
								{product.title} — ${product.price}
							</button>
						</li>
					{/each}
				</ul>

				<div class="modal-action">
					<button class="btn" on:click={closeProductModal}>Cancel</button>
				</div>
			</div>
		</dialog>
	{/if}
{:else if errorMessage}
	<p class="text-red-500">{errorMessage}</p>
{:else}
	<p>Loading order...</p>
{/if}

<style>
	/* без стилей */
</style>
