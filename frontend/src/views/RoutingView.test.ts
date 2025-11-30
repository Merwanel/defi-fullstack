import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RoutingView from './RoutingView.vue';
import { request } from '../client';

vi.mock('../client', () => ({
    request: vi.fn()
}));

describe('RoutingView', () => {
    it('should render form fields', () => {
        const wrapper = mount(RoutingView);
        expect(wrapper.find('input#from').exists()).toBe(true);
        expect(wrapper.find('input#to').exists()).toBe(true);
        expect(wrapper.find('input#analytic').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('should call request on submit', async () => {
        const wrapper = mount(RoutingView);

        await wrapper.find('input#from').setValue('A');
        await wrapper.find('input#to').setValue('B');
        await wrapper.find('input#analytic').setValue('CODE');

        (request as any).mockResolvedValue({
            id: '1',
            fromStationId: 'A',
            toStationId: 'B',
            analyticCode: 'CODE',
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
                analyticCode: 'CODE'
            })
        });
    });

    it('should display result on success', async () => {
        const wrapper = mount(RoutingView);

        (request as any).mockResolvedValue({
            id: '1',
            fromStationId: 'A',
            toStationId: 'B',
            analyticCode: 'CODE',
            distanceKm: 10,
            path: ['A', 'B'],
            createdAt: '2023-01-01'
        });

        await wrapper.find('form').trigger('submit');
        // Wait for promises to resolve
        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.result').exists()).toBe(true);
        expect(wrapper.text()).toContain('10 km');
    });

    it('should display error on failure', async () => {
        const wrapper = mount(RoutingView);

        (request as any).mockRejectedValue(new Error('Network Error'));

        await wrapper.find('form').trigger('submit');
        // Wait for promises to resolve
        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.error').exists()).toBe(true);
        expect(wrapper.text()).toContain('Network Error');
    });
});
