import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import AnalyticsView from './AnalyticsView.vue';
import * as client from '../client';
import type { AnalyticDistanceList } from '../types';

vi.mock('../client');

describe('AnalyticsView', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders the form with all inputs', () => {
        const wrapper = mount(AnalyticsView);

        expect(wrapper.find('h2').text()).toBe('Analytics');
        expect(wrapper.find('#from-date').exists()).toBe(true);
        expect(wrapper.find('#to-date').exists()).toBe(true);
        expect(wrapper.find('#group-by').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('builds query params correctly with all filters', async () => {
        const mockResponse: AnalyticDistanceList = {
            from: '2024-01-01',
            to: '2024-12-31',
            groupBy: 'month',
            items: [
                {
                    analyticCode: 'fret',
                    totalDistanceKm: 1500,
                    group: '2024-01',
                    periodStart: '2024-01-01',
                    periodEnd: '2024-01-31'
                }
            ]
        };

        vi.mocked(client.request).mockResolvedValue(mockResponse);

        const wrapper = mount(AnalyticsView);

        await wrapper.find('#from-date').setValue('2024-01-01');
        await wrapper.find('#to-date').setValue('2024-12-31');
        await wrapper.find('#group-by').setValue('month');

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(client.request).toHaveBeenCalledWith(
            expect.stringContaining('/stats/distances?')
        );

        const callArg = vi.mocked(client.request).mock.calls[0][0] as string;
        expect(callArg).toContain('from=2024-01-01');
        expect(callArg).toContain('to=2024-12-31');
        expect(callArg).toContain('groupBy=month');
    });

    it('builds query params correctly with no filters', async () => {
        const mockResponse: AnalyticDistanceList = {
            groupBy: 'none',
            items: []
        };

        vi.mocked(client.request).mockResolvedValue(mockResponse);

        const wrapper = mount(AnalyticsView);

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        const callArg = vi.mocked(client.request).mock.calls[0][0] as string;
        expect(callArg).toBe('/stats/distances?');
    });

    it('renders data in table when results are returned', async () => {
        const mockResponse: AnalyticDistanceList = {
            groupBy: 'none',
            items: [
                { analyticCode: 'fret', totalDistanceKm: 1500 },
                { analyticCode: 'passager', totalDistanceKm: 2300 }
            ]
        };

        vi.mocked(client.request).mockResolvedValue(mockResponse);

        const wrapper = mount(AnalyticsView);

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('table').exists()).toBe(true);
        expect(wrapper.findAll('tbody tr')).toHaveLength(2);
        expect(wrapper.html()).toContain('fret');
        expect(wrapper.html()).toContain('1500');
        expect(wrapper.html()).toContain('passager');
        expect(wrapper.html()).toContain('2300');
    });

    it('renders period column when groupBy is set', async () => {
        const mockResponse: AnalyticDistanceList = {
            groupBy: 'day',
            items: [
                {
                    analyticCode: 'fret',
                    totalDistanceKm: 500,
                    group: '2024-01-15',
                    periodStart: '2024-01-15',
                    periodEnd: '2024-01-15'
                }
            ]
        };

        vi.mocked(client.request).mockResolvedValue(mockResponse);

        const wrapper = mount(AnalyticsView);

        await wrapper.find('#group-by').setValue('day');
        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(wrapper.findAll('th')).toHaveLength(3);
        expect(wrapper.html()).toContain('Period');
        expect(wrapper.html()).toContain('2024-01-15');
    });

    it('displays error message when request fails', async () => {
        vi.mocked(client.request).mockRejectedValue(new Error('Network error'));

        const wrapper = mount(AnalyticsView);

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.error').exists()).toBe(true);
        expect(wrapper.find('.error').text()).toContain('Network error');
    });

    it('shows loading state during request', async () => {
        vi.mocked(client.request).mockImplementation(() => new Promise(() => { }));

        const wrapper = mount(AnalyticsView);

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('button').text()).toBe('Loading...');
        expect(wrapper.find('button').attributes('disabled')).toBeDefined();
    });

    it('displays "no data" message when items array is empty', async () => {
        const mockResponse: AnalyticDistanceList = {
            groupBy: 'none',
            items: []
        };

        vi.mocked(client.request).mockResolvedValue(mockResponse);

        const wrapper = mount(AnalyticsView);

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('table').exists()).toBe(false);
        expect(wrapper.html()).toContain('No data found for the selected criteria');
    });

    it('displays raw JSON in details element', async () => {
        const mockResponse: AnalyticDistanceList = {
            groupBy: 'none',
            items: [{ analyticCode: 'fret', totalDistanceKm: 1500 }]
        };

        vi.mocked(client.request).mockResolvedValue(mockResponse);

        const wrapper = mount(AnalyticsView);

        await wrapper.find('form').trigger('submit.prevent');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('details').exists()).toBe(true);
        expect(wrapper.find('summary').text()).toBe('Raw JSON');
        expect(wrapper.find('pre').text()).toContain('"analyticCode": "fret"');
    });
});
