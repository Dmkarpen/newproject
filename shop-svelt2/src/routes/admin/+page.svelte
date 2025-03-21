<script>
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';

	let orders = [];

	onMount(async () => {
		const res = await fetch('http://127.0.0.1:8000/api/orders');
		orders = await res.json();
	});

	function editOrder(id) {
		goto(`/admin/orders/${id}`);
	}

	async function deleteOrder(id) {
		try {
			// Отправляем DELETE-запрос
			const res = await fetch(`http://127.0.0.1:8000/api/orders/${id}`, {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json'
				}
			});

			if (res.ok) {
				// Удаляем заказ из локального массива
				orders = orders.filter((o) => o.id !== id);
			} else {
				console.error('Error deleting order:', await res.text());
			}
		} catch (err) {
			console.error('Error deleting order:', err);
		}
	}
</script>

<h3 class="page-title">Orders List</h3>
{#if orders.length === 0}
	<p>No orders found.</p>
{:else}
	<table class="orders-table">
		<thead>
			<tr>
				<th>ID</th>
				<th>Customer Name</th>
				<th>Total</th>
				<th>Status</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			{#each orders as order}
				<tr>
					<td>{order.id}</td>
					<td>{order.name}</td>
					<td>{order.total}</td>
					<td>{order.status}</td>
					<td>
						<button class="edit-btn" on:click={() => editOrder(order.id)}>Edit</button>
						<button class="delete-btn" on:click={() => deleteOrder(order.id)}>Delete</button>
					</td>
				</tr>
			{/each}
		</tbody>
	</table>
{/if}

<style>
	/* заголовок */
	.page-title {
		font-size: 1.5rem; /* больше размер */
		font-weight: bold; /* жирный */
		margin-bottom: 1rem; /* небольшой отступ снизу */
	}

	/* Стили для таблицы */
	.orders-table {
		width: 100%;
		border-collapse: collapse; /* Убирает двойные границы между ячейками */
		margin-top: 1rem;
	}

	/* Границы вокруг ячеек */
	.orders-table th,
	.orders-table td {
		border: 1px solid #ccc; /* Серые границы */
		padding: 0.5rem; /* Отступ внутри ячеек */
		text-align: left; /* Текст по левому краю */
	}

	/* Можно стилизовать заголовок */
	.orders-table th {
		background-color: #f2f2f2; /* Светло-серый фон */
		font-weight: bold;
	}

	/* Общие стили для кнопок */
	.orders-table button {
		padding: 0.4rem 0.8rem;
		border: none;
		border-radius: 4px;
		cursor: pointer;
		margin-right: 0.4rem;
	}

	/* Кнопка Edit (зелёная) */
	.edit-btn {
		background-color: #4caf50; /* Зелёный */
		color: white;
	}
	.edit-btn:hover {
		background-color: #45a049; /* Тёмнее при наведении */
	}

	/* Кнопка Delete (красная) */
	.delete-btn {
		background-color: #f44336; /* Красный */
		color: white;
	}
	.delete-btn:hover {
		background-color: #e53935; /* Тёмнее при наведении */
	}
</style>
