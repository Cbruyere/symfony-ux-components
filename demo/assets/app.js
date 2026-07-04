import './stimulus_bootstrap.js';
import { Application } from '@hotwired/stimulus';
import LiveController from '@symfony/ux-live-component';
import HSStaticMethods from 'preline';

const app = Application.start();

app.register('live', LiveController);
window.addEventListener('load', () => {
    HSStaticMethods.autoInit();
});
