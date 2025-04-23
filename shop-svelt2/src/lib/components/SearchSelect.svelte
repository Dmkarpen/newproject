<script>
	import { onMount, onDestroy } from 'svelte';

	export let items = [];
	export let selected = null;
	export let placeholder = 'Select...';
	export let label = 'label';
	export let minSearchLength = 3;
	export let useStartsWith = false;

	let query = '';
	let isOpen = false;
	let wrapper;
	let wasInitialized = false;

	$: if (selected && !wasInitialized) {
		query = selected[label];
		wasInitialized = true;
	}

	$: if (selected === null) {
		query = '';
	}

	$: filtered =
		query.length >= minSearchLength || minSearchLength === 0
			? items.filter((item) =>
					useStartsWith
						? item[label].toLowerCase().startsWith(query.toLowerCase())
						: item[label].toLowerCase().includes(query.toLowerCase())
				)
			: [];

	function selectItem(item) {
		selected = item;
		isOpen = false;
		query = item[label];
	}

	function handleClickOutside(event) {
		if (!wrapper.contains(event.target)) {
			isOpen = false;
		}
	}

	onMount(() => {
		document.addEventListener('click', handleClickOutside);
	});

	onDestroy(() => {
		document.removeEventListener('click', handleClickOutside);
	});
</script>

<div class="relative w-full" bind:this={wrapper}>
	<input
		type="text"
		class="input input-bordered w-full"
		{placeholder}
		bind:value={query}
		on:focus={() => {
			if (minSearchLength === 0) isOpen = true;
		}}
		on:input={() => (isOpen = query.length >= minSearchLength)}
	/>

	{#if isOpen}
		<ul
			class="absolute z-10 bg-white border border-gray-300 rounded-md mt-1 max-h-60 overflow-auto w-full shadow"
		>
			{#if query.length > 0 && filtered.length === 0}
				<li class="px-4 py-2 text-gray-400">No results found</li>
			{:else}
				{#each filtered as item}
					<li on:click={() => selectItem(item)} class="px-4 py-2 hover:bg-gray-100 cursor-pointer">
						{item[label]}
					</li>
				{/each}
			{/if}
		</ul>
	{/if}

	{#if query.length > 0 && query.length < minSearchLength}
		<p class="text-sm text-gray-500 mt-1">Enter at least 3 characters</p>
	{/if}
</div>
