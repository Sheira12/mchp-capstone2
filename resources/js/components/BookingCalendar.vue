<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold text-gray-800">Booking Calendar</h3>
      <div class="flex items-center gap-2">
        <button @click="prevMonth" class="p-1 hover:bg-gray-100 rounded">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <span class="font-medium text-sm">{{ monthName }} {{ year }}</span>
        <button @click="nextMonth" class="p-1 hover:bg-gray-100 rounded">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>

    <!-- Day headers -->
    <div class="grid grid-cols-7 gap-1 mb-2">
      <div v-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day"
           class="text-center text-xs font-medium text-gray-500 py-1">
        {{ day }}
      </div>
    </div>

    <!-- Calendar grid -->
    <div class="grid grid-cols-7 gap-1">
      <div v-for="(cell, i) in calendarCells" :key="i"
           class="min-h-16 p-1 rounded-lg border"
           :class="cell.isCurrentMonth ? 'bg-white border-gray-100' : 'bg-gray-50 border-transparent'">
        <div v-if="cell.date" class="text-xs font-medium mb-1"
             :class="cell.isToday ? 'text-blue-700 font-bold' : 'text-gray-600'">
          {{ cell.date.getDate() }}
        </div>
        <div v-for="booking in cell.bookings" :key="booking.id"
             class="text-xs px-1 py-0.5 rounded mb-0.5 truncate cursor-pointer"
             :style="{ backgroundColor: booking.color + '20', color: booking.color }"
             :title="booking.title"
             @click="openBooking(booking)">
          {{ booking.title }}
        </div>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex items-center gap-4 mt-4 text-xs text-gray-500">
      <div class="flex items-center gap-1">
        <div class="w-3 h-3 rounded" style="background: #d9770620;"></div>
        <span>Pending</span>
      </div>
      <div class="flex items-center gap-1">
        <div class="w-3 h-3 rounded" style="background: #16a34a20;"></div>
        <span>Confirmed</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const today = new Date();
const month = ref(today.getMonth());
const year  = ref(today.getFullYear());
const bookings = ref([]);

const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const monthName  = computed(() => monthNames[month.value]);

async function fetchBookings() {
  try {
    const res = await axios.get('/api/bookings/calendar-events');
    bookings.value = res.data;
  } catch (e) {
    console.error('Failed to load bookings', e);
  }
}

const calendarCells = computed(() => {
  const cells = [];
  const firstDay = new Date(year.value, month.value, 1);
  const lastDay  = new Date(year.value, month.value + 1, 0);

  // Pad start
  for (let i = 0; i < firstDay.getDay(); i++) {
    cells.push({ date: null, isCurrentMonth: false, bookings: [] });
  }

  // Days of month
  for (let d = 1; d <= lastDay.getDate(); d++) {
    const date = new Date(year.value, month.value, d);
    const dateStr = date.toISOString().split('T')[0];
    const dayBookings = bookings.value.filter(b => b.start === dateStr);
    cells.push({
      date,
      isCurrentMonth: true,
      isToday: dateStr === today.toISOString().split('T')[0],
      bookings: dayBookings,
    });
  }

  return cells;
});

function prevMonth() {
  if (month.value === 0) { month.value = 11; year.value--; }
  else month.value--;
}

function nextMonth() {
  if (month.value === 11) { month.value = 0; year.value++; }
  else month.value++;
}

function openBooking(booking) {
  if (booking.url) window.location.href = booking.url;
}

onMounted(fetchBookings);
</script>
