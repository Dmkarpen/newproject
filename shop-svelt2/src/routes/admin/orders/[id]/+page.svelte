<script>
	import { page } from '$app/stores';
	import { onMount } from 'svelte';

	let orderId = $page.params.id;
	let order = null;

	// Сообщения об успехе/ошибке
	let successMessage = '';
	let errorMessage = '';

	// Загружаем заказ при монтировании
	onMount(async () => {
		try {
			const res = await fetch(`http://127.0.0.1:8000/api/orders/${orderId}`);
			if (!res.ok) {
				errorMessage = 'Error loading order: ' + (await res.text());
				return;
			}
			order = await res.json();
		} catch (err) {
			errorMessage = 'Error fetching order: ' + err;
		}
	});

	// Реактивный пересчёт total (округление до 2 знаков)
	$: if (order && order.items) {
		let sum = 0;
		for (const item of order.items) {
			sum += item.price * item.count;
		}
		order.total = parseFloat(sum.toFixed(2));
	}

	// Сохранить изменения (PUT /api/orders/{id})
	async function updateOrder() {
		successMessage = '';
		errorMessage = '';

		const body = {
			name: order.name,
			phone: order.phone,
			address: order.address,
			total: order.total,
			status: order.status,
			// Отправляем все items
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
		}
	}

	// Добавить новый товар (id=null), пусть админ укажет product_id, count
	// (title/price подтянутся на бэкенде или при сохранении)
	function addItem() {
		if (!order.items) {
			order.items = [];
		}
		order.items.push({
			id: null,
			product_id: '',
			title: '', // не редактируем на фронте
			price: 0, // не редактируем на фронте
			count: 1
		});
		// Обновляем ссылку, чтобы Svelte увидел изменения
		order.items = [...order.items];
	}

	// Удалить товар (DELETE /api/orders/{orderId}/items/{itemId})
	async function removeItem(item, index) {
		successMessage = '';
		errorMessage = '';

		// Если id=null => товар ещё не в БД
		if (!item.id) {
			order.items.splice(index, 1);
			order.items = [...order.items];
			return;
		}

		// Иначе делаем запрос DELETE
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
		}
	}
</script>

{#if order}
	<h3 class="page-title">Edit Order {order.id}</h3>

	<!-- Сообщения -->
	{#if successMessage}
		<p class="success-msg">{successMessage}</p>
	{/if}
	{#if errorMessage}
		<p class="error-msg">{errorMessage}</p>
	{/if}

	<!-- Поля заказа -->
	<div class="edit-form">
		<div class="form-group">
			<label>Name:</label>
			<input bind:value={order.name} />
		</div>

		<div class="form-group">
			<label>Phone:</label>
			<input bind:value={order.phone} />
		</div>

		<div class="form-group">
			<label>Address:</label>
			<input bind:value={order.address} />
		</div>

		<div class="form-group">
			<label>Total:</label>
			<input type="number" bind:value={order.total} disabled />
		</div>

		<div class="form-group">
			<label>Status:</label>
			<select bind:value={order.status}>
				<option value="pending">Pending</option>
				<option value="processing">Processing</option>
				<option value="completed">Completed</option>
				<option value="cancelled">Cancelled</option>
			</select>
		</div>
	</div>

	<h4 class="page-title">Order Items</h4>
	<table class="items-table">
		<thead>
			<tr>
				<th>ID</th>
				<th>Product ID</th>
				<!-- title / price отображаем, но без возможности редактирования -->
				<th>Title</th>
				<th>Price</th>
				<th>Count</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			{#each order.items as item, index}
				<tr>
					<td>{item.id}</td>
					<td>
						<input type="number" bind:value={item.product_id} />
					</td>
					<!-- Просто текст, нельзя менять -->
					<td>{item.title}</td>
					<td>{item.price}</td>
					<td>
						<input type="number" min="1" bind:value={item.count} />
					</td>
					<td>
						<button class="delete-btn" on:click={() => removeItem(item, index)}>Remove</button>
					</td>
				</tr>
			{/each}
		</tbody>
	</table>

	<!-- Кнопка "Add Item" (зелёная) -->
	<button class="add-btn" on:click={addItem}>Add item</button>

	<!-- Кнопка "Save" (синий, как раньше) -->
	<button class="save-btn" on:click={updateOrder}>Save</button>
{:else if errorMessage}
	<p class="error-msg">{errorMessage}</p>
{:else}
	<p>Loading order...</p>
{/if}

<style>
	/* заголовок */
	.page-title {
		font-size: 1.5rem; /* больше размер */
		font-weight: bold; /* жирный */
		margin-bottom: 1rem; /* небольшой отступ снизу */
	}

	/* Общие стили формы */
	.edit-form {
		max-width: 400px;
		background-color: #fafafa;
		border: 1px solid #ddd;
		padding: 1rem;
		border-radius: 4px;
		margin-bottom: 1rem;
	}

	.form-group {
		margin-bottom: 1rem;
		display: flex;
		flex-direction: column;
	}

	.form-group label {
		font-weight: bold;
		margin-bottom: 0.3rem;
	}

	.form-group input,
	.form-group select {
		padding: 0.5rem;
		border: 1px solid #ccc;
		border-radius: 4px;
	}

	/* Стили таблицы */
	.items-table {
		border-collapse: collapse;
		width: 100%;
		margin-top: 1rem;
	}

	.items-table th,
	.items-table td {
		border: 1px solid #ccc;
		padding: 0.5rem;
		text-align: left;
	}

	.items-table th {
		background-color: #f2f2f2;
		font-weight: bold;
	}

	.items-table input[type='number'] {
		width: 60px; /* или сколько нужно */
	}

	/* Сообщения */
	.success-msg {
		color: green;
		background-color: #e6ffe6;
		border: 1px solid green;
		padding: 0.5rem;
		margin-bottom: 1rem;
	}
	.error-msg {
		color: red;
		background-color: #ffe5e5;
		border: 1px solid red;
		padding: 0.5rem;
		margin-bottom: 1rem;
	}

	/* Кнопка "Save" (синяя, как было) */
	.save-btn {
		margin-top: 1rem;
		padding: 0.6rem 1rem;
		background-color: #007bff;
		color: #fff;
		border: none;
		border-radius: 4px;
		cursor: pointer;
	}
	.save-btn:hover {
		background-color: #0056b3;
	}

	/* Кнопка "Add item" (зелёная) */
	.add-btn {
		margin-right: 1rem;
		padding: 0.6rem 1rem;
		background-color: #4caf50; /* Зелёный */
		color: #fff;
		border: none;
		border-radius: 4px;
		cursor: pointer;
	}
	.add-btn:hover {
		background-color: #45a049;
	}

	/* Кнопка "Remove" (красная) */
	.delete-btn {
		background-color: #f44336; /* Красный */
		color: #fff;
		border: none;
		border-radius: 4px;
		padding: 0.4rem 0.8rem;
		cursor: pointer;
	}
	.delete-btn:hover {
		background-color: #e53935;
	}
</style>
