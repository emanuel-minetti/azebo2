import { ApiService } from "/src/services/index";

export default class PrintService extends ApiService {
  static printMonth(month: Date) {
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const yearNumber = month.getFullYear();
    const monthNumber = month.getMonth() + 1;
    const url = this.getBaseUrl() + "print" + "/" + yearNumber + "/" + monthNumber;
    const requestOptions = {
      method: "POST",
      headers: headers,
    }
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}