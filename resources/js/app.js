const relativeTimeFormatter = new Intl.RelativeTimeFormat('fr', {
    numeric: 'always',
});

const relativeTimeUnits = [
    ['year', 1000 * 60 * 60 * 24 * 365],
    ['month', 1000 * 60 * 60 * 24 * 30],
    ['week', 1000 * 60 * 60 * 24 * 7],
    ['day', 1000 * 60 * 60 * 24],
    ['hour', 1000 * 60 * 60],
    ['minute', 1000 * 60],
    ['second', 1000],
];

const formatRelativeTime = (date, mode = 'relative') => {
    const diff = date.getTime() - Date.now();
    const absDiff = Math.abs(diff);

    if (mode === 'due' && diff <= 0) {
        return 'maintenant';
    }

    if (absDiff < 1000) {
        return 'maintenant';
    }

    const [unit, unitMs] = relativeTimeUnits.find(([, unitMs]) => absDiff >= unitMs) ?? ['second', 1000];
    const value = Math.round(diff / unitMs);

    return relativeTimeFormatter.format(value, unit);
};

const refreshRelativeTimes = () => {
    document.querySelectorAll('[data-relative-time]').forEach((element) => {
        const date = new Date(element.dataset.relativeTime);

        if (Number.isNaN(date.getTime())) {
            return;
        }

        element.textContent = formatRelativeTime(date, element.dataset.relativeTimeMode);
    });
};

const startRelativeTimes = () => {
    refreshRelativeTimes();

    if (window.upCheckerRelativeTimeInterval) {
        return;
    }

    window.upCheckerRelativeTimeInterval = window.setInterval(refreshRelativeTimes, 1000);
};

document.addEventListener('DOMContentLoaded', startRelativeTimes);
document.addEventListener('livewire:navigated', startRelativeTimes);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
