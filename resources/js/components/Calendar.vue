<template>
    <div class="min-h-screen bg-gray-900 p-4 text-gray-100">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Верхнее меню -->
            <div class="bg-gray-800 rounded-xl shadow-2xl p-4">
                <div class="flex flex-col space-y-4">
                    <!-- Выбор сауны -->
                    <div class="sm:w-full md:hidden relative">
                        <select 
                            v-model="selectedSauna" 
                            @change="handleSaunaChange" 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-blue-600 text-white
                                   focus:ring-2 focus:ring-blue-500/20 transition-all duration-200
                                   hover:bg-blue-700 cursor-pointer appearance-none"
                        >
                            <option value="" disabled>Выберите сауну</option>
                            <option v-for="sauna in saunas" :key="sauna.id" :value="sauna.id"
                                    class="bg-gray-800 text-gray-100 py-2">
                                {{ sauna.name }}
                            </option>
                        </select>
                        <i class="fa fa-home absolute left-3 top-1/2 -translate-y-1/2 text-white/80"></i>
                        <i class="fa fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-white/80"></i>
                    </div>

                    <!-- Десктопная версия выбора сауны -->
                    <div class="hidden md:block relative">
                        <select 
                            v-model="selectedSauna" 
                            @change="handleSaunaChange" 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 bg-blue-600 text-white
                                focus:ring-2 focus:ring-blue-500/20 transition-all duration-200
                                hover:bg-blue-700 cursor-pointer appearance-none"
                        >
                                <option value="" disabled>Выберите сауну</option>
                                <option v-for="sauna in saunas" :key="sauna.id" :value="sauna.id"
                                        class="bg-gray-800 text-gray-100 py-2">
                                    {{ sauna.name }}
                                </option>
                            </select>
                            <i class="fa fa-home absolute left-3 top-1/2 -translate-y-1/2 text-white/80"></i>
                            <i class="fa fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-white/80"></i>
                        </div>

                    <!-- Навигация календаря -->
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <button 
                                @click="prevPeriod"
                                class="p-2 rounded-lg bg-gray-700/50 text-gray-300 hover:bg-gray-600 transition-colors"
                            >
                                <i class="fa fa-chevron-left "></i>
                            </button>
                            <button 
                                @click="nextPeriod"
                                class="p-2 rounded-lg bg-gray-700/50 text-gray-300 hover:bg-gray-600 transition-colors"
                            >
                                <i class="fa fa-chevron-right"></i>
                            </button>
                            <button 
                                @click="goToToday"
                                class="px-3 py-2 rounded-lg bg-gray-700/50 text-gray-300 hover:bg-gray-600 transition-colors"
                            >
                                Сегодня
                            </button>
                        </div>
                        <div class="hidden sm:block md:w-1/2">
                        </div>
                        <!-- Переключатель вида -->
                        <div class="flex items-center bg-gray-700/50 rounded-lg p-1">
                            <button 
                                @click="switchView('timeGridWeek')"
                                class="px-3 py-1.5 rounded-md transition-all duration-200 flex items-center gap-2"
                                :class="{ 'bg-blue-600 text-white': calendarOptions.initialView === 'timeGridWeek', 'text-gray-400 hover:text-gray-200': calendarOptions.initialView !== 'timeGridWeek' }"
                            >
                                <i class="mdi mdi-calendar-week text-lg"></i>
                                <span class="hidden sm:inline">Неделя</span>
                            </button>
                            <button 
                                @click="switchView('timeGridDay')"
                                class="px-3 py-1.5 rounded-md transition-all duration-200 flex items-center gap-2"
                                :class="{ 'bg-blue-600 text-white': calendarOptions.initialView === 'timeGridDay', 'text-gray-400 hover:text-gray-200': calendarOptions.initialView !== 'timeGridDay' }"
                            >
                                <i class="mdi mdi-calendar-today text-lg"></i>
                                <span class="hidden sm:inline">День</span>
                            </button>
                        </div>
                    </div>

                    <!-- Кнопка создания -->
                    <button 
                        @click="openCreateModal"
                        class="w-full group px-6 py-2.5 bg-blue-600 text-white rounded-xl 
                               hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 
                               focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200 
                               flex items-center justify-center gap-2"
                    >
                        <i class="mdi mdi-plus text-xl group-hover:rotate-90 transition-transform duration-300"></i>
                        <span>Создать запись</span>
                    </button>
                </div>
            </div>

            <!-- Календарь -->
            <div class="bg-gray-800 rounded-xl shadow-2xl overflow-hidden border border-gray-600">
                <div class="calendar-container">
                    <FullCalendar
                        ref="fullCalendar"
                        :options="calendarOptions"
                        class="calendar-height"
                    />
                </div>
            </div>
        </div>

        <!-- Модальное окно создания записи -->
        <div v-if="showModal" 
             class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             @click="showModal = false"
        >
            <div 
                class="w-full max-w-lg transform bg-gray-800 rounded-xl shadow-2xl border border-gray-700 transition-all"
                @click.stop
            >
                <div class="relative p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-100">
                            Создание брони
                        </h3>
                        <button 
                            @click="showModal = false"
                            class="p-2 text-gray-400 hover:text-gray-300 rounded-lg hover:bg-gray-700 transition-all duration-200"
                        >
                            <i class="mdi mdi-close text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Форма -->
                    <div class="space-y-4">
                        <!-- Имя клиента -->
                        <div class="form-group">
                            <label class="form-label">
                                Имя клиента
                            </label>
                            <div class="relative">
                                <i class="mdi mdi-account absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    v-model="bookingForm.client_name" 
                                    type="text" 
                                    class="form-input"
                                    :class="{ 'error': validationErrors.client_name }"
                                >
                            </div>
                            <p v-if="validationErrors.client_name" class="error-message">
                                {{ validationErrors.client_name[0] }}
                            </p>
                        </div>

                        <!-- Телефон -->
                        <div class="form-group">
                            <label class="form-label">
                                Телефон
                            </label>
                            <div class="relative">
                                <i class="mdi mdi-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    v-model="bookingForm.client_phone" 
                                    type="tel" 
                                    class="form-input"
                                    :class="{ 'error': validationErrors.client_phone }"
                                >
                            </div>
                            <p v-if="validationErrors.client_phone" class="error-message">
                                {{ validationErrors.client_phone[0] }}
                            </p>
                        </div>

                        <!-- Время начала -->
                        <div class="form-group">
                            <label class="form-label">
                                Начало
                            </label>
                            <div class="relative">
                                <i class="mdi mdi-clock-start absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    id="start_datetime"
                                    type="text" 
                                    class="form-input"
                                    :class="{ 'error': validationErrors.start_datetime }"
                                    placeholder="Выберите дату и время начала"
                                >
                            </div>
                            <p v-if="validationErrors.start_datetime" class="error-message">
                                {{ validationErrors.start_datetime[0] }}
                            </p>
                        </div>

                        <!-- Время окончания -->
                        <div class="form-group">
                            <label class="form-label">
                                Окончание
                            </label>
                            <div class="relative">
                                <i class="mdi mdi-clock-end absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    id="end_datetime"
                                    type="text" 
                                    class="form-input"
                                    :class="{ 'error': validationErrors.end_datetime }"
                                    placeholder="Выберите дату и время окончания"
                                >
                            </div>
                            <p v-if="validationErrors.end_datetime" class="error-message">
                                {{ validationErrors.end_datetime[0] }}
                            </p>
                        </div>

                        <!-- Комментарий -->
                        <div class="form-group">
                            <label class="form-label">
                                Комментарий
                            </label>
                            <div class="relative">
                                <i class="mdi mdi-comment-text absolute left-3 top-3 text-gray-400"></i>
                                <textarea 
                                    v-model="bookingForm.comment" 
                                    rows="3"
                                    class="form-input"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="mt-6 flex justify-end gap-3">
                        <button 
                            @click="showModal = false"
                            class="btn-secondary"
                        >
                            <i class="mdi mdi-close"></i>
                            Отмена
                        </button>
                        <button 
                            @click="createBooking"
                            :disabled="isLoading"
                            class="btn-primary"
                        >
                            <i class="mdi mdi-check"></i>
                            {{ isLoading ? 'Создание...' : 'Создать' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import flatpickr from 'flatpickr'
import 'flatpickr/dist/flatpickr.css'
import { Russian } from 'flatpickr/dist/l10n/ru.js'

export default defineComponent({
    name: 'Calendar',
    components: {
        FullCalendar
    },
    data() {
        return {
            saunas: [],
            selectedSauna: '',
            showModal: false,
            isLoading: false,
            validationErrors: {},
            bookingForm: {
                sauna_id: '',
                client_name: '',
                client_phone: '',
                start_datetime: '',
                end_datetime: '',
                comment: ''
            },
            calendarOptions: {
                plugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin
                ],
                initialView: 'timeGridWeek',
                slotMinTime: '00:00:00',
                slotMaxTime: '24:00:00',
                allDaySlot: false,
                selectable: true,
                selectMirror: true,
                editable: false,
                dayMaxEvents: true,
                selectConstraint: {
                    start: new Date().toISOString(),
                    end: '2100-01-01'
                },
                selectOverlap: false,
                eventOverlap: false,
                headerToolbar: false, // Отключаем встроенную панель управления
                slotDuration: '01:00:00',
                selectMinDistance: 1000,
                locale: 'ru',
                events: this.fetchEvents,
                select: this.handleDateSelect,
                eventClick: this.handleEventClick,
                eventsSet: this.handleEvents,
                nowIndicator: true,
                height: 'auto',
                views: {
                    timeGridWeek: {
                        titleFormat: { year: 'numeric', month: 'long', day: '2-digit' }
                    },
                    timeGridDay: {
                        titleFormat: { year: 'numeric', month: 'long', day: '2-digit' }
                    }
                }
            },
            startPicker: null,
            endPicker: null,
            currentEvents: []
        }
    },
    watch: {
        selectedSauna(newValue) {
            console.log('selectedSauna changed to:', newValue)
            this.onSaunaChange()
        }
    },
    methods: {
        getInitialView() {
            return window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek'
        },
        openCreateModal() {
            this.showModal = true
        },
        openBookingModal() {
            const now = new Date()
            now.setMinutes(0)
            now.setSeconds(0)
            
            const end = new Date(now)
            end.setHours(end.getHours() + 1)
            
            this.bookingForm = {
                sauna_id: this.selectedSauna,
                client_name: '',
                client_phone: '',
                start_datetime: now.toISOString().slice(0, 16),
                end_datetime: end.toISOString().slice(0, 16),
                comment: ''
            }
            this.showModal = true
        },
        handleEventClick(clickInfo) {
            // Показываем информацию о существующей записи
            alert(`
                Бронирование
                Время: ${this.formatDateTime(clickInfo.event.start)}
                До: ${this.formatDateTime(clickInfo.event.end)}
            `)
        },
        handleEvents(events) {
            this.currentEvents = events
            console.log('Current events updated:', events)
        },
        async fetchEvents() {
            if (!this.selectedSauna) {
                return []
            }

            try {
                const response = await fetch(`/api/bookings?sauna_id=${this.selectedSauna}`)
                const bookings = await response.json()
                
                return bookings.map(booking => ({
                    id: booking.id,
                    title: 'Забронировано',
                    start: booking.start_datetime,
                    end: booking.end_datetime,
                    backgroundColor: '#ef4444',
                    borderColor: '#dc2626'
                }))
            } catch (error) {
                console.error('Error fetching events:', error)
                return []
            }
        },
        async handleDateSelect(selectInfo) {
            const now = new Date()
            const selectedStart = new Date(selectInfo.start)
            
            // Проверяем, что выбранное время не раньше текущего
            if (selectedStart < now) {
                alert('Нельзя создать бронь на прошедшее время')
                return
            }
            
            // Форматируем даты в MySQL формат
            const startDate = new Date(selectInfo.start)
            const endDate = new Date(selectInfo.end)
            
            this.bookingForm = {
                sauna_id: this.selectedSauna,
                client_name: '',
                client_phone: '',
                start_datetime: startDate.toISOString().slice(0, 19).replace('T', ' '),
                end_datetime: endDate.toISOString().slice(0, 19).replace('T', ' '),
                comment: ''
            }
            console.log('Booking form initialized:', this.bookingForm)
            this.showModal = true
        },
        async createBooking() {
            if (!this.selectedSauna) {
                alert('Выберите сауну')
                return
            }

            try {
                this.isLoading = true
                console.log('Sending booking data:', this.bookingForm)
                
                const response = await fetch('/api/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.bookingForm)
                })

                if (!response.ok) {
                    const error = await response.json()
                    throw new Error(error.message || 'Ошибка при создании бронирования')
                }

                const booking = await response.json()
                console.log('Created booking:', booking)

                // Добавляем событие в календарь
                if (this.$refs.fullCalendar) {
                    const calendarApi = this.$refs.fullCalendar.getApi()
                    calendarApi.addEvent({
                        id: booking.id,
                        title: 'Забронировано',
                        start: booking.start_datetime,
                        end: booking.end_datetime,
                        backgroundColor: '#ef4444',
                        borderColor: '#dc2626'
                    })
                }

                this.showModal = false
                this.resetForm()
            } catch (error) {
                console.error('Error creating booking:', error)
                alert(error.message)
            } finally {
                this.isLoading = false
            }
        },
        async deleteBooking() {
            if (!this.selectedBooking) return
            if (!confirm('Вы уверены, что хотите удалить эту бронь?')) return

            try {
                this.isLoading = true
                const response = await fetch(`/api/bookings/${this.selectedBooking.bookingId}`, {
                    method: 'DELETE'
                })

                if (!response.ok) {
                    throw new Error('Ошибка при удалении брони')
                }

                this.selectedBooking.event.remove()
                this.showBookingInfo = false
                this.selectedBooking = null
            } catch (error) {
                console.error('Error deleting booking:', error)
                alert('Произошла ошибка при удалении брони')
            } finally {
                this.isLoading = false
            }
        },
        calculateEndTime(startTime) {
            const date = new Date(startTime)
            date.setHours(date.getHours() + 1)
            return date.toTimeString().split(' ')[0]
        },
        formatDateTime(date) {
            if (!date) return ''
            return new Date(date).toLocaleString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        },
        async loadSaunas() {
            try {
                const response = await fetch('/api/saunas')
                this.saunas = await response.json()
                if (this.saunas.length > 0 && !this.selectedSauna) {
                    this.selectedSauna = this.saunas[0].id
                    await this.fetchEvents() // Загружаем события для выбранной сауны
                }
            } catch (error) {
                console.error('Error loading saunas:', error)
            }
        },
        async loadEvents() {
            if (!this.selectedSauna) return

            try {
                const response = await fetch(`/api/bookings?sauna_id=${this.selectedSauna}`)
                const bookings = await response.json()
                console.log('Received bookings:', bookings)

                const events = bookings.map(booking => ({
                    id: booking.id,
                    title: 'Забронировано',
                    start: booking.start_datetime,
                    end: booking.end_datetime,
                    backgroundColor: '#ef4444',
                    borderColor: '#dc2626'
                }))

                console.log('Generated events:', events)

                // Обновляем события в календаре
                if (this.$refs.fullCalendar) {
                    const calendarApi = this.$refs.fullCalendar.getApi()
                    calendarApi.removeAllEvents()
                    
                    // Добавляем события напрямую
                    events.forEach(event => {
                        try {
                            calendarApi.addEvent(event)
                            console.log('Added event:', event)
                        } catch (e) {
                            console.error('Error adding event:', e, event)
                        }
                    })
                    
                    // Проверяем события после добавления
                    const addedEvents = calendarApi.getEvents()
                    console.log('Events in calendar after adding:', addedEvents.length, addedEvents)
                }
            } catch (error) {
                console.error('Error loading events:', error)
            }
        },
        initializeCalendar() {
            if (this.$refs.fullCalendar) {
                const calendarApi = this.$refs.fullCalendar.getApi()
                calendarApi.setOption('initialView', this.getInitialView())
                calendarApi.setOption('height', window.innerHeight - 200)
                this.loadEvents()
            }
        },
        async onSaunaChange() {
            if (this.$refs.fullCalendar) {
                const calendarApi = this.$refs.fullCalendar.getApi()
                calendarApi.refetchEvents()
            }
        },
        resetForm() {
            this.bookingForm = {
                sauna_id: this.selectedSauna,
                client_name: '',
                client_phone: '',
                start_datetime: '',
                end_datetime: '',
                comment: ''
            }
            this.validationErrors = {}
        },
        getMinStartTime() {
            const now = new Date()
            return now.toISOString().slice(0, 16) // Формат YYYY-MM-DDTHH:mm для input datetime-local
        },
        initializeDatePickers() {
            const now = new Date()
            const config = {
                enableTime: true,
                time_24hr: true,
                dateFormat: "Y-m-d H:i",
                locale: Russian,
                minuteIncrement: 60,
                minDate: now,
                onChange: this.handleDateChange
            }

            this.startPicker = flatpickr("#start_datetime", {
                ...config,
                onChange: (selectedDates) => {
                    if (selectedDates[0]) {
                        this.bookingForm.start_datetime = this.formatDateTime(selectedDates[0])
                        // Устанавливаем минимальную дату для конца брони
                        this.endPicker.set('minDate', selectedDates[0])
                    }
                }
            })

            this.endPicker = flatpickr("#end_datetime", {
                ...config,
                onChange: (selectedDates) => {
                    if (selectedDates[0]) {
                        this.bookingForm.end_datetime = this.formatDateTime(selectedDates[0])
                    }
                }
            })
        },
        handleDateChange() {
            console.log('Date changed')
        },
        switchView(view) {
            const calendar = this.$refs.fullCalendar.getApi()
            calendar.changeView(view)
            this.calendarOptions.initialView = view
        },
        prevPeriod() {
            const calendar = this.$refs.fullCalendar.getApi()
            calendar.prev()
        },
        nextPeriod() {
            const calendar = this.$refs.fullCalendar.getApi()
            calendar.next()
        },
        goToToday() {
            const calendar = this.$refs.fullCalendar.getApi()
            calendar.today()
        }
    },
    mounted() {
        this.loadSaunas()
        console.log('Calendar mounted, ref exists:', !!this.$refs.fullCalendar)
        this.initializeCalendar()
        this.loadEvents()
        this.initializeDatePickers()
    },
    updated() {
        if (this.selectedSauna) {
            this.loadEvents()
        }
    }
})
</script>

<style>
/* Base styles */
.form-group {
    @apply space-y-1;
}

.form-label {
    @apply block text-sm font-medium text-gray-300 mb-1;
}

.form-input {
    @apply w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-700 bg-gray-800 text-gray-100
           focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all duration-200;
}

.form-input.error {
    @apply border-red-500 focus:border-red-500 focus:ring-red-500/20;
}

.error-message {
    @apply text-sm text-red-400 mt-1;
}

.btn-primary {
    @apply flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl 
           hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 
           focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-50 
           disabled:cursor-not-allowed transition-all duration-200;
}

.btn-secondary {
    @apply flex items-center gap-2 px-6 py-2.5 bg-gray-700 text-gray-300 rounded-xl
           hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500
           focus:ring-offset-2 focus:ring-offset-gray-800 transition-all duration-200;
}

/* Calendar styles */
.calendar-container {
    @apply relative;
}

.calendar-height {
    min-height: 700px;
}

:deep(.fc) {
    @apply font-sans text-gray-100;
}

:deep(.fc-theme-standard) {
    @apply border-gray-600/50;
}

:deep(.fc-theme-standard td, .fc-theme-standard th) {
    @apply border-gray-600/30;
}

:deep(.fc-toolbar-title) {
    @apply text-xl font-semibold text-gray-100 !important;
}

:deep(.fc-button-primary) {
    @apply bg-gray-700/80 border-gray-600/50 font-medium rounded-xl !important;
    @apply hover:bg-gray-600 hover:border-gray-500 !important;
    @apply focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-800 !important;
    @apply transition-all duration-200 !important;
}

:deep(.fc-timegrid-slot) {
    @apply h-20 border-gray-600/30 !important;
}

:deep(.fc-timegrid-slot-lane) {
    @apply bg-gray-800/50 transition-colors duration-200;
}

:deep(.fc-timegrid-slot-lane:nth-child(even)) {
    @apply bg-gray-700/30;
}

:deep(.fc-highlight) {
    @apply bg-blue-900/20 !important;
}

:deep(.fc-event) {
    @apply bg-blue-600/90 border-blue-700/50 rounded-lg !important;
    @apply shadow-sm cursor-pointer !important;
    @apply transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md !important;
}

:deep(.fc-event-title) {
    @apply font-medium !important;
}

:deep(.fc-timegrid-now-indicator-line) {
    @apply border-red-500/50 !important;
}

:deep(.fc-timegrid-now-indicator-arrow) {
    @apply border-red-500/50 !important;
}

:deep(.fc-timegrid-axis) {
    @apply text-base !important;
    padding: 1rem !important;
}

:deep(.fc-col-header-cell) {
    @apply py-4 !important;
}

:deep(.fc-col-header-cell-cushion) {
    @apply text-base !important;
}

/* Flatpickr dark theme */
:deep(.flatpickr-calendar) {
    @apply bg-gray-800 border border-gray-700 rounded-xl shadow-xl !important;
}

:deep(.flatpickr-months) {
    @apply bg-gray-800 text-gray-100 !important;
}

:deep(.flatpickr-weekdays) {
    @apply bg-gray-800 text-gray-100 !important;
}

:deep(.flatpickr-day) {
    @apply text-gray-100 !important;
}

:deep(.flatpickr-day.selected) {
    @apply bg-blue-600 border-blue-600 !important;
}

:deep(.flatpickr-day:hover) {
    @apply bg-gray-700 !important;
}

:deep(.flatpickr-time) {
    @apply bg-gray-800 text-gray-100 !important;
}

/* Responsive styles */
@media (max-width: 640px) {
    :deep(.fc-toolbar) {
        @apply flex-col gap-2 !important;
    }

    :deep(.fc-toolbar-title) {
        @apply text-lg !important;
    }

    :deep(.fc-button) {
        @apply text-sm px-3 py-1 !important;
    }
}
</style>
