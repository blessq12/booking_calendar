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
                        <select v-model="bookingForm.sauna_id" class="form-control">
                            <option v-for="sauna in saunas" :key="sauna.id" :value="sauna.id">
                                {{ sauna.name }}
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Имя клиента</label>
                        <input type="text" v-model="bookingForm.client_name" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="tel" v-model="bookingForm.client_phone" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Дата и время начала</label>
                        <input type="datetime-local" v-model="bookingForm.start_datetime" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Дата и время окончания</label>
                        <input type="datetime-local" v-model="bookingForm.end_datetime" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button @click="closeModal" class="btn-cancel">Отмена</button>
                    <button @click="createBooking" class="btn-submit">Создать запись</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue'
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
    data() {
        return {
            saunas: [],
            selectedSauna: '',
            showModal: false,
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
                initialView: this.getInitialView(),
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
                events: [],
                eventClick: this.handleEventClick,
                eventsSet: this.handleEvents,
                editable: false,
                height: 'auto',
                expandRows: true,
                stickyHeaderDates: true,
                nowIndicator: true,
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
                Запись на: ${this.formatDateTime(clickInfo.event.start)}
                До: ${this.formatDateTime(clickInfo.event.end)}
                Статус: ${clickInfo.event.extendedProps.status}
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
                
                const events = data.map(schedule => {
                    return schedule.slots.map(slot => ({
                        id: `${schedule.id}-${slot.time}`,
                        title: slot.status === 'available' ? 'Свободно' : 'Занято',
                        start: `${schedule.date}T${slot.time}`,
                        end: slot.booking_end ? `${schedule.date}T${slot.booking_end}` : this.calculateEndTime(`${schedule.date}T${slot.time}`),
                        status: slot.status,
                        backgroundColor: slot.status === 'available' ? '#4CAF50' : '#F44336'
                    }))
                }).flat()

                this.calendarOptions.events = events
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
            try {
                const response = await fetch('/api/bookings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        sauna_id: this.bookingForm.sauna_id,
                        client_name: this.bookingForm.client_name,
                        client_phone: this.bookingForm.client_phone,
                        start: this.bookingForm.start_datetime,
                        end: this.bookingForm.end_datetime
                    })
                })

                if (response.ok) {
                    this.closeModal()
                    this.loadEvents()
                    alert('Запись успешно создана!')
                } else {
                    const error = await response.json()
                    alert(error.message || 'Ошибка при создании записи')
                }
            } catch (error) {
                console.error('Error creating booking:', error)
                alert('Ошибка при создании записи')
            }
        },
        closeModal() {
            this.showModal = false
            this.bookingForm = {
                sauna_id: this.selectedSauna,
                client_name: '',
                client_phone: '',
                start_datetime: '',
                end_datetime: ''
            }
        }
    },
    mounted() {
        this.loadSaunas()
        
        // Обработка изменения размера экрана
        window.addEventListener('resize', () => {
            this.calendarOptions.initialView = this.getInitialView()
        })
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
</style>
