<template>
  <div class="relative">
    <input
      v-model="query"
      @input="search"
      @focus="showDropdown = true"
      @blur="hideDropdown"
      type="text"
      :placeholder="placeholder"
      class="form-input w-full"
      autocomplete="off"
    />
    <input type="hidden" :name="fieldName" :value="selectedId">

    <div v-if="showDropdown && results.length > 0"
         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
      <button v-for="item in results" :key="item.id"
              @mousedown.prevent="select(item)"
              class="w-full text-left px-4 py-2.5 hover:bg-blue-50 text-sm border-b border-gray-50 last:border-0">
        <span class="font-medium text-gray-900">{{ item.text }}</span>
        <span v-if="item.extra" class="text-gray-400 ml-2 text-xs">{{ item.extra }}</span>
      </button>
    </div>

    <div v-if="showDropdown && query.length >= 2 && results.length === 0 && !loading"
         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg px-4 py-3 text-sm text-gray-400">
      No parishioners found.
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  fieldName:   { type: String, default: 'parishioner_id' },
  placeholder: { type: String, default: 'Search parishioner...' },
  initialId:   { type: Number, default: null },
  initialName: { type: String, default: '' },
});

const query        = ref(props.initialName);
const selectedId   = ref(props.initialId);
const results      = ref([]);
const showDropdown = ref(false);
const loading      = ref(false);
let debounceTimer  = null;

function search() {
  selectedId.value = null;
  clearTimeout(debounceTimer);
  if (query.value.length < 2) { results.value = []; return; }

  debounceTimer = setTimeout(async () => {
    loading.value = true;
    try {
      const res = await axios.get('/admin/parishioners/search', { params: { q: query.value } });
      results.value = res.data;
    } catch {}
    loading.value = false;
  }, 300);
}

function select(item) {
  query.value      = item.text;
  selectedId.value = item.id;
  results.value    = [];
  showDropdown.value = false;
}

function hideDropdown() {
  setTimeout(() => { showDropdown.value = false; }, 150);
}
</script>
