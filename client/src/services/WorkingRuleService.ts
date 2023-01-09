import ApiService from "/src/services/ApiService";

export default class WorkingRuleService extends ApiService {
  static get() {
    const url = this.getBaseUrl() + "working-rule";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }

  static setRule(rule: any) {
    const url = this.getBaseUrl() + "working-rule";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "POST",
      headers: headers,
      body: JSON.stringify(rule),
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
