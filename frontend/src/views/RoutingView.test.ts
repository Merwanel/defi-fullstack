
import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { ref } from 'vue';
import RoutingView from './RoutingView.vue';
import { request } from '../client';
import ItineraryView from './ItineraryView.vue';

const { mockedUseStations } = vi.hoisted(() => {
    return { mockedUseStations: vi.fn() }
});

vi.mock('../client', () => ({
    request: vi.fn()
}));

vi.mock('../composables/useStations', () => ({
    useStations: mockedUseStations
}));

mockedUseStations.mockReturnValue({
    stations: ref([
        { id: 'A', shortName: 'A', longName: 'Station A' },
        { id: 'B', shortName: 'B', longName: 'Station B' }
    ]),
    stationsHashMap: ref(new Map([
        ['A', { id: 'A', shortName: 'A', longName: 'Station A' }],
        ['B', { id: 'B', shortName: 'B', longName: 'Station B' }]
    ])),
    loading: ref(false),
    error: ref(null)
});

describe('RoutingView', () => {
    it('should render form fields', () => {
        const wrapper = mount(RoutingView);
        expect(wrapper.find('select#from').exists()).toBe(true);
        expect(wrapper.find('select#to').exists()).toBe(true);
        expect(wrapper.find('select#analytic').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('should call request on submit', async () => {
        const wrapper = mount(RoutingView);

        await wrapper.find('select#from').setValue('A');
        await wrapper.find('select#to').setValue('B');
        await wrapper.find('select#analytic').setValue('fret');

        vi.mocked(request).mockResolvedValue({
            id: '1',
            fromStationId: 'A',
            toStationId: 'B',
            analyticCode: 'fret',
            distanceKm: 10,
            path: ['A', 'B'],
            createdAt: '2023-01-01'
        });

        await wrapper.find('form').trigger('submit');

        expect(request).toHaveBeenCalledWith('/routes', {
            method: 'POST',
            body: JSON.stringify({
                fromStationId: 'A',
                toStationId: 'B',
                analyticCode: 'fret'
            })
        });
    });

    it('should display result on success', async () => {
        const wrapper = mount(RoutingView);

        vi.mocked(request).mockResolvedValue({
            id: '1',
            fromStationId: 'A',
            toStationId: 'B',
            analyticCode: 'CODE',
            distanceKm: 10,
            path: ['A', 'B'],
            createdAt: '2023-01-01'
        });

        await wrapper.find('form').trigger('submit');

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('10 km');
        expect(wrapper.findComponent(ItineraryView).exists()).toBe(true);
    });

    it('should display error on failure', async () => {
        const wrapper = mount(RoutingView);

        vi.mocked(request).mockRejectedValue(new Error('Network Error'));

        await wrapper.find('form').trigger('submit');

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.error').exists()).toBe(true);
        expect(wrapper.text()).toContain('Network Error');
    });
});
