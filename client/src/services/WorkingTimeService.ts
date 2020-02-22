import { ApiService } from "@/services";
import { WorkingDay } from "@/models";

export default class WorkingTimeService extends ApiService {
  /**
   * Sends a request for month to the API and handles the response.
   *
   * Returns a `Promise<String>`.
   * @param year the year of the month to request data for
   * @param month the (one based) month to request data for
   */
  static getMonth(year: String, month: String) {
    const url = this.getBaseUrl() + "working-time/" + year + "/" + month;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }

  static setDay(day: WorkingDay) {
    const url = this.getBaseUrl() + "working-time";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "POST",
      headers: headers,
      body: JSON.stringify(day)
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
