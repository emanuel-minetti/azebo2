import { ApiService } from "@/services/index";

export default class WorkingTimeService extends ApiService {
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
}
