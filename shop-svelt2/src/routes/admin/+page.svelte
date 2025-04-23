<script>
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import AppLoader from '$lib/components/AppLoader.svelte';
	import { isLoading } from '$lib/stores/loading';

	let orders = [];
	let showConfirmModal = false;
	let selectedOrderId = null;

	let search = '';
	let selectedStatus = '';
	let selectedDelivery = '';
	const statuses = ['pending', 'processing', 'completed', 'cancelled'];
	const deliveryTypes = ['pickup', 'courier'];

	onMount(() => {
		fetchOrders();
	});

	async function fetchOrders() {
		isLoading.set(true);
		try {
			let url = new URL('http://127.0.0.1:8000/api/orders');
			if (search) url.searchParams.set('search', search);
			if (selectedStatus) url.searchParams.set('status', selectedStatus);
			if (selectedDelivery) url.searchParams.set('delivery_type', selectedDelivery);

			const res = await fetch(url);
			if (!res.ok) throw new Error('Failed to fetch orders');
			orders = await res.json();
		} catch (e) {
			console.error('Failed to load orders:', e);
		} finally {
			isLoading.set(false);
		}
	}

	function handleSearch() {
		fetchOrders();
	}

	function clearFilters() {
		search = '';
		selectedStatus = '';
		selectedDelivery = '';
		fetchOrders();
	}

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

<!-- Форма пошуку та фільтрації -->
<div class="flex flex-col md:flex-row gap-4 mb-6 items-center">
	<input
		type="text"
		class="input input-bordered w-full md:w-64"
		placeholder="Search by ID or Name"
		bind:value={search}
		on:keypress={(e) => {
			if (e.key === 'Enter') handleSearch();
		}}
	/>

	<select
		class="select select-bordered w-full md:w-48"
		bind:value={selectedStatus}
		on:change={fetchOrders}
	>
		<option value="">All Statuses</option>
		{#each statuses as status}
			<option value={status}>{status}</option>
		{/each}
	</select>

	<select
		class="select select-bordered w-full md:w-48"
		bind:value={selectedDelivery}
		on:change={fetchOrders}
	>
		<option value="">All delivery types</option>
		{#each deliveryTypes as type}
			<option value={type}>{type}</option>
		{/each}
	</select>

	<button class="btn btn-primary" on:click={handleSearch}>Search</button>
	{#if search || selectedStatus || selectedDelivery}
		<button class="btn btn-outline" on:click={clearFilters}>Clear</button>
	{/if}
</div>

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
					<th>Delivery</th>
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
						<td>{order.delivery_type || '-'}</td>
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
				<button class="btn btn-error" on:click={deleteOrderConfirmed}>Yes</button>
				<button class="btn" on:click={cancelDelete}>No</button>
			</div>
		</div>
	</dialog>
{/if}

<style>
	/* без стилей */
</style>
