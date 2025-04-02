<script>
	export let items = [];
	export let selected = null;
	export let placeholder = 'Виберіть...';
	export let label = 'label';

	let query = '';
	let isOpen = false;

	$: filtered =
		query.length === 0
			? items
			: items.filter((item) => item[label].toLowerCase().includes(query.toLowerCase()));

	function selectItem(item) {
		selected = item;
		isOpen = false;
		query = item[label];
	}

	function toggleDropdown() {
		isOpen = !isOpen;
	}
</script>

<div class="relative w-full">
	<input
		type="text"
		class="input input-bordered w-full"
		{placeholder}
		bind:value={query}
		on:focus={() => (isOpen = true)}
		on:input={() => (isOpen = true)}
	/>

	{#if isOpen}
		<ul
			class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 max-h-60 overflow-auto w-full shadow"
		>
			{#each filtered as item}
				<li on:click={() => selectItem(item)} class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
					{item[label]}
				</li>
			{/each}

			{#if filtered.length === 0}
				<li class="px-4 py-2 text-gray-400">Нічого не знайдено</li>
			{/if}
		</ul>
	{/if}
</div>
