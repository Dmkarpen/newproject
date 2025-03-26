<script>
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import AppLoader from '$lib/components/AppLoader.svelte';
	import { isLoading } from '$lib/stores/loading';

	let orders = [];
	let showConfirmModal = false;
	let selectedOrderId = null;

	onMount(async () => {
		isLoading.set(true);
		try {
			const res = await fetch('http://127.0.0.1:8000/api/orders');
			orders = await res.json();
		} catch (e) {
			console.error('Failed to load orders:', e);
		} finally {
			isLoading.set(false);
		}
	});

	function editOrder(id) {
		goto(`/admin/orders/${id}`);
	}

	function confirmDelete(id) {
		selectedOrderId = id;
		showConfirmModal = true;
	}

	function cancelDelete() {
		selectedOrderId = null;
		showConfirmModal = false;
	}

	async function deleteOrderConfirmed() {
		if (!selectedOrderId) return;
		isLoading.set(true);

		try {
			const res = await fetch(`http://127.0.0.1:8000/api/orders/${selectedOrderId}`, {
				method: 'DELETE',
				headers: {
					'Content-Type': 'application/json'
				}
			});

			if (res.ok) {
				orders = orders.filter((o) => o.id !== selectedOrderId);
			} else {
				console.error('Error deleting order:', await res.text());
			}
		} catch (err) {
			console.error('Error deleting order:', err);
		} finally {
			isLoading.set(false);
			showConfirmModal = false;
			selectedOrderId = null;
		}
	}
</script>

<AppLoader />

<h2 class="text-2xl font-bold mb-6">Orders List</h2>

{#if orders.length === 0}
	<p class="text-gray-500">No orders found.</p>
{:else}
	<div class="overflow-x-auto">
		<table class="table table-zebra w-full">
			<thead>
				<tr>
					<th>ID</th>
					<th>Customer</th>
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
						<td>${order.total}</td>
						<td>{order.status}</td>
						<td class="space-x-2">
							<button class="btn btn-sm btn-success" on:click={() => editOrder(order.id)}
								>Edit</button
							>
							<button class="btn btn-sm btn-error" on:click={() => confirmDelete(order.id)}
								>Delete</button
							>
						</td>
					</tr>
				{/each}
			</tbody>
		</table>
	</div>
{/if}

<!-- Modal -->
{#if showConfirmModal}
	<dialog class="modal modal-open">
		<div class="modal-box">
			<h3 class="font-bold text-lg">Confirm Delete</h3>
			<p class="py-4">Are you sure you want to delete this order?</p>
			<div class="modal-action">
				<!-- Ніякої додаткової логіки тут не потрібно -->
				<button class="btn btn-error" on:click={deleteOrderConfirmed}>Yes</button>
				<button class="btn" on:click={cancelDelete}>No</button>
			</div>
		</div>
	</dialog>
{/if}

<style>
	/* без стилей */
</style>
