<template>
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="header-controls">
                <select v-model="selectedSauna" class="sauna-select">
                    <option value="">Выберите сауну</option>
                    <option v-for="sauna in saunas" :key="sauna.id" :value="sauna.id">
                        {{ sauna.name }}
                    </option>
                </select>
                <button @click="openBookingModal" class="btn-create" :disabled="!selectedSauna">
                    Создать запись
                </button>
            </div>
        </div>

        <FullCalendar 
            ref="fullCalendar"
            :options="calendarOptions"
            class="calendar"
        />

        <!-- Модальное окно для создания записи -->
        <div v-if="showModal" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <h3>Создание записи</h3>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Сауна</label>
                        <select v-model="bookingForm.sauna_id" class="form-control" :class="{ 'error': validationErrors.sauna_id }">
                            <option v-for="sauna in saunas" :key="sauna.id" :value="sauna.id">
                                {{ sauna.name }}
                            </option>
                        </select>
                        <span class="error-message" v-if="validationErrors.sauna_id">{{ validationErrors.sauna_id[0] }}</span>
                    </div>

                    <div class="form-group">
                        <label>Имя клиента</label>
                        <input type="text" v-model="bookingForm.client_name" class="form-control" :class="{ 'error': validationErrors.client_name }">
                        <span class="error-message" v-if="validationErrors.client_name">{{ validationErrors.client_name[0] }}</span>
                    </div>

                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" v-model="bookingForm.client_phone" v-maska data-maska="+7 (###) ###-##-##" class="form-control" :class="{ 'error': validationErrors.client_phone }">
                        <span class="error-message" v-if="validationErrors.client_phone">{{ validationErrors.client_phone[0] }}</span>
                    </div>

                    <div class="form-group">
                        <label>Дата и время начала</label>
                        <input 
                            type="datetime-local" 
                            v-model="bookingForm.start_datetime" 
                            @change="handleStartDateChange"
                            class="form-control" 
                            :class="{ 'error': validationErrors.start }"
                        >
                        <span class="error-message" v-if="validationErrors.start">{{ validationErrors.start[0] }}</span>
                    </div>

                    <div class="form-group">
                        <label>Дата и время окончания</label>
                        <input 
                            type="datetime-local" 
                            v-model="bookingForm.end_datetime" 
                            @change="handleEndDateChange"
                            class="form-control" 
                            :class="{ 'error': validationErrors.end }"
                        >
                        <span class="error-message" v-if="validationErrors.end">{{ validationErrors.end[0] }}</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="closeModal" class="btn-cancel" :disabled="isLoading">Отмена</button>
                    <button @click="createBooking" class="btn-submit" :disabled="isLoading">
                        {{ isLoading ? 'Создание...' : 'Создать запись' }}
                    </button>
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
import ruLocale from '@fullcalendar/core/locales/ru'

export default defineComponent({
    name: 'Calendar',
    components: {
        FullCalendar
    },
    setup() {
        const fullCalendar = ref(null)
        return { fullCalendar }
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
                end_datetime: ''
            },
            calendarOptions: {
                plugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    interactionPlugin
                ],
                initialView: 'timeGridWeek',
                locale: ruLocale,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay,timeGridWeek,dayGridMonth'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '22:00:00',
                slotDuration: '01:00:00',
                selectable: false,
                selectMirror: true,
                dayMaxEvents: true,
                weekends: true,
                eventClick: this.handleEventClick,
                editable: false,
                height: 'auto',
                expandRows: true,
                stickyHeaderDates: true,
                nowIndicator: true,
                allDaySlot: false,
                views: {
                    timeGridWeek: {
                        titleFormat: { year: 'numeric', month: 'long', day: 'numeric' }
                    }
                }
            },
            currentEvents: []
        }
    },
    watch: {
        selectedSauna() {
            this.loadEvents()
            this.bookingForm.sauna_id = this.selectedSauna
        }
    },
    methods: {
        getInitialView() {
            return window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek'
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
                end_datetime: end.toISOString().slice(0, 16)
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
        },
        async loadSaunas() {
            try {
                const response = await fetch('/api/saunas')
                this.saunas = await response.json()
            } catch (error) {
                console.error('Error loading saunas:', error)
            }
        },
        async loadEvents() {
            if (!this.selectedSauna) return

            try {
                const response = await fetch(`/api/schedules?sauna_id=${this.selectedSauna}`)
                const data = await response.json()
                console.log('Received data:', data)

                const events = data.map(schedule => {
                    const slots = Array.isArray(schedule.slots) ? schedule.slots : []
                    console.log('Schedule slots:', slots)

                    const bookedSlots = slots.filter(slot => slot && slot.status === 'booked')
                    console.log('Booked slots:', bookedSlots)

                    return bookedSlots.map(slot => {
                        const date = schedule.date
                        const startTime = slot.time || '00:00:00'
                        const endTime = slot.booking_end || this.calculateEndTime(`${date}T${startTime}`).split('T')[1]
                        
                        const eventStart = `${date}T${startTime}`
                        const eventEnd = `${date}T${endTime}`

                        console.log('Event times:', { eventStart, eventEnd })

                        return {
                            id: `${schedule.id}-${slot.time}`,
                            title: 'Забронировано',
                            start: eventStart,
                            end: eventEnd,
                            backgroundColor: '#F44336',
                            borderColor: '#D32F2F',
                            extendedProps: {
                                client_id: slot.client_id
                            }
                        }
                    })
                }).flat()

                console.log('Generated events:', events)

                if (this.$refs.fullCalendar) {
                    const calendarApi = this.$refs.fullCalendar.getApi()
                    calendarApi.removeAllEvents()
                    events.forEach(event => {
                        calendarApi.addEvent(event)
                    })
                }
            } catch (error) {
                console.error('Error loading events:', error)
            }
        },
        calculateEndTime(startTime) {
            const date = new Date(startTime)
            date.setHours(date.getHours() + 1)
            return date.toISOString()
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
        async createBooking() {
            this.isLoading = true;
            this.validationErrors = {};

            try {
                const response = await fetch('/api/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        sauna_id: this.bookingForm.sauna_id,
                        client_name: this.bookingForm.client_name,
                        client_phone: this.bookingForm.client_phone,
                        start: this.bookingForm.start_datetime,
                        end: this.bookingForm.end_datetime
                    })
                });

                const data = await response.json();

                if (response.status === 422) {
                    this.validationErrors = data.errors || {};
                    throw new Error(data.message || 'Ошибка валидации');
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Ошибка при создании записи');
                }

                // Закрываем модальное окно
                this.closeModal();
                
                // Обновляем календарь
                await this.loadEvents();
                
                // Показываем сообщение об успехе
                alert('Запись успешно создана!');
            } catch (error) {
                console.error('Error creating booking:', error);
                if (!Object.keys(this.validationErrors).length) {
                    alert(error.message || 'Ошибка при создании записи');
                }
            } finally {
                this.isLoading = false;
            }
        },
        closeModal() {
            this.showModal = false;
            this.validationErrors = {};
            this.bookingForm = {
                sauna_id: this.selectedSauna,
                client_name: '',
                client_phone: '',
                start_datetime: '',
                end_datetime: ''
            };
        },
        handleStartDateChange() {
            const start = new Date(this.bookingForm.start_datetime)
            const end = new Date(this.bookingForm.end_datetime)
            
            // Если конечная дата раньше начальной или разница меньше часа
            if (end <= start || (end - start) < 3600000) { // 3600000 мс = 1 час
                const newEnd = new Date(start)
                newEnd.setHours(newEnd.getHours() + 1)
                this.bookingForm.end_datetime = newEnd.toISOString().slice(0, 16)
            }
        },
        handleEndDateChange() {
            const start = new Date(this.bookingForm.start_datetime)
            const end = new Date(this.bookingForm.end_datetime)
            
            // Если конечная дата раньше начальной или разница меньше часа
            if (end <= start || (end - start) < 3600000) {
                const newStart = new Date(end)
                newStart.setHours(newStart.getHours() - 1)
                this.bookingForm.start_datetime = newStart.toISOString().slice(0, 16)
            }
        },
        initializeCalendar() {
            // Инициализация календаря после монтирования
            if (this.$refs.fullCalendar) {
                const calendarApi = this.$refs.fullCalendar.getApi()
                calendarApi.setOption('height', 'auto')
                if (this.selectedSauna) {
                    this.loadEvents()
                }
            }
        }
    },
    mounted() {
        this.loadSaunas()
        this.initializeCalendar()
    },
    updated() {
        if (this.selectedSauna) {
            this.loadEvents()
        }
    }
})
</script>

<style scoped>
.calendar-container {
    padding: 10px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.calendar-header {
    margin-bottom: 15px;
}

.header-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.sauna-select {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.btn-create {
    padding: 8px 16px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.btn-create:disabled {
    background: #cccccc;
    cursor: not-allowed;
}

.calendar {
    height: calc(100vh - 150px);
    min-height: 500px;
}

/* Модальное окно */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
}

.modal-body {
    margin: 20px 0;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.btn-cancel, .btn-submit {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.btn-cancel {
    background: #f5f5f5;
}

.btn-submit {
    background: #4CAF50;
    color: white;
}

/* Адаптивные стили для FullCalendar */
:deep(.fc) {
    font-family: 'Arial', sans-serif;
}

:deep(.fc-toolbar-title) {
    font-size: 1.2em !important;
    font-weight: 600;
}

:deep(.fc-button) {
    padding: 6px 12px !important;
    font-size: 14px !important;
    background-color: #4CAF50 !important;
    border-color: #4CAF50 !important;
}

:deep(.fc-button:hover) {
    background-color: #45a049 !important;
}

:deep(.fc-timegrid-slot) {
    height: 60px !important;
}

:deep(.fc-event) {
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    font-size: 14px;
}

:deep(.fc-event:hover) {
    opacity: 0.9;
}

/* Мобильные стили */
@media (max-width: 768px) {
    .calendar-container {
        padding: 5px;
    }

    .header-controls {
        flex-direction: column;
    }

    .btn-create {
        width: 100%;
    }

    :deep(.fc-toolbar) {
        flex-direction: column;
        gap: 10px;
    }

    :deep(.fc-toolbar-title) {
        font-size: 1em !important;
    }

    :deep(.fc-button) {
        padding: 4px 8px !important;
        font-size: 12px !important;
    }

    :deep(.fc-timegrid-slot) {
        height: 50px !important;
    }

    :deep(.fc-event) {
        font-size: 12px;
    }
}

.error {
    border-color: #dc3545;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
    display: block;
}

.form-control:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
}

.btn-submit:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}
</style>
