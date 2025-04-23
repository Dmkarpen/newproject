<script>
	import SearchSelect from '$lib/components/SearchSelect.svelte';

	export let deliveryType;
	export let cities = [];
	export let warehouses = [];

	export let selectedCity;
	export let selectedWarehouse;
	export let address;
</script>

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
